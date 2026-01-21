<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\Employee\Attendance as EmployeeAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    /**
     * Process attendance logs for a specific date range and/or user
     * 
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @param int|null $userId
     * @param bool $forceReprocess If true, will delete existing attendance records and redo them
     */
    public function processLogs($dateFrom = null, $dateTo = null, $userId = null, $forceReprocess = false)
    {
        $startTime = microtime(true);
        
        $query = AttendanceLog::query()->whereNotNull('user_id');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($dateFrom) {
            $query->where('datetime', '>=', Carbon::parse($dateFrom)->startOfDay());
        }

        if ($dateTo) {
            $query->where('datetime', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        if (!$forceReprocess) {
            $query->whereNull('processed_at');
        }

        // We group by user and date to process them together
        $logs = $query->orderBy('datetime')->get();
        
        if ($logs->isEmpty()) {
            return [
                'success' => true,
                'processed_count' => 0,
                'message' => 'Tidak ada data log baru untuk diproses.'
            ];
        }

        $groupedLogs = $logs->groupBy(function($log) {
            return $log->user_id . '_' . $log->datetime->format('Y-m-d');
        });

        $processedCount = 0;
        $errorCount = 0;

        foreach ($groupedLogs as $groupKey => $userLogs) {
            DB::beginTransaction();

            try {
                $firstLog = $userLogs->first();
                $date = $firstLog->datetime->format('Y-m-d');
                $uid = $firstLog->user_id;

                // 1. Get or create attendance record
                $attendance = EmployeeAttendance::firstOrNew([
                    'user_id' => $uid,
                    'date' => $date,
                ]);

                // 2. Determine Check-In and Check-Out
                // We fetch ALL logs for this user/date (including previously processed) 
                // to ensure we identify the correct first/last tap
                $allLogsForDay = AttendanceLog::where('user_id', $uid)
                    ->whereDate('datetime', $date)
                    ->orderBy('datetime')
                    ->get();

                $this->calculateAttendanceTimes($attendance, $allLogsForDay);

                // 3. Update details (hours, status) using the model's shift-aware logic
                $attendance->total_hours = $attendance->calculateTotalHours();
                $attendance->overtime_hours = $attendance->calculateOvertimeHours();
                
                // Status determined by shift-aware logic in model/service
                $status = $this->determineStatus($attendance);
                $attendance->status = $status;

                // 4. Generate clean notes
                $attendance->notes = $this->generateNotes($attendance, $status);

                $attendance->save();

                // 4. Mark logs as processed
                AttendanceLog::whereIn('id', $userLogs->pluck('id'))->update(['processed_at' => now()]);

                DB::commit();
                $processedCount++;
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('AttendanceService Error: ' . $e->getMessage(), [
                    'group' => $groupKey,
                    'trace' => $e->getTraceAsString()
                ]);
                $errorCount++;
            }
        }

        $executionTime = round(microtime(true) - $startTime, 2);

        return [
            'success' => true,
            'processed_count' => $processedCount,
            'error_count' => $errorCount,
            'execution_time' => $executionTime,
            'message' => "Berhasil memproses {$processedCount} data absensi ({$executionTime} detik)."
        ];
    }

    private function calculateAttendanceTimes(EmployeeAttendance $attendance, $logs)
    {
        $shift = $attendance->effective_shift;
        $cutoffHour = 12;

        if ($shift && $shift->start_time && $shift->end_time) {
            $start = Carbon::parse($shift->start_time);
            $end = Carbon::parse($shift->end_time);
            if ($end->lt($start)) $end->addDay();
            
            $duration = $start->diffInMinutes($end);
            $midpoint = $start->copy()->addMinutes($duration / 2);
            $cutoffHour = $midpoint->hour;
        }

        $firstTap = null;
        $lastTap = null;

        foreach ($logs as $log) {
            $time = $log->datetime;
            
            // Assign to check-in if before midpoint and earlier than current check-in
            if ($time->hour < $cutoffHour) {
                if (!$firstTap || $time->lt($firstTap)) {
                    $firstTap = $time;
                }
            } 
            // Assign to check-out if after midpoint and later than current check-out
            else {
                if (!$lastTap || $time->gt($lastTap)) {
                    $lastTap = $time;
                }
            }
        }

        // Rule 4: Checkout-only scenario
        // If only tap after 2 PM (14:00) and no shift, treat as checkout-only (not counted as attendance)
        if (!$firstTap && $lastTap && !$shift) {
            if ($lastTap->hour >= 14) {
                // This is checkout-only, don't count as check-in
                $attendance->check_in_time = null;
                $attendance->check_out_time = $lastTap;
                return;
            }
        }

        // If only one tap exists and it's before cutoff, assign as check-in
        if (!$firstTap && !$lastTap && $logs->isNotEmpty()) {
            $singleTap = $logs->first()->datetime;
            if ($singleTap->hour < $cutoffHour) {
                $firstTap = $singleTap;
            } else {
                $lastTap = $singleTap;
            }
        }

        $attendance->check_in_time = $firstTap;
        $attendance->check_out_time = $lastTap;
    }

    private function determineStatus(EmployeeAttendance $attendance)
    {
        if (!$attendance->check_in_time) {
            return 'absent';
        }

        $shift = $attendance->effective_shift;
        $date = $attendance->date->format('Y-m-d');
        
        // Get shift times or use global defaults
        if ($shift) {
            $workStartTime = Carbon::parse($date . ' ' . $shift->start_time);
            $lateToleranceMinutes = $shift->late_tolerance_minutes ?? 5;
        } else {
            // Use global settings
            $workStartTime = Carbon::parse($date . ' ' . \App\Models\AttendanceSetting::getValue('work_start_time', '08:00'));
            $lateToleranceMinutes = \App\Models\AttendanceSetting::getValue('late_tolerance_minutes', 5);
        }

        $checkInTime = $attendance->check_in_time;
        
        // Rule 1: Early arrival - 20 minutes or more before work start time
        $earlyThreshold = $workStartTime->copy()->subMinutes(20);
        if ($checkInTime->lte($earlyThreshold)) {
            return 'early_arrival';
        }
        
        // Rule 2: On-time - between 19 minutes before and tolerance after work start time
        $lateThreshold = $workStartTime->copy()->addMinutes($lateToleranceMinutes);
        if ($checkInTime->lte($lateThreshold)) {
            return 'on_time';
        }
        
        // Rule 3: Late - after tolerance threshold
        return 'late';
    }

    /**
     * Generate clean, human-readable notes for the attendance record
     */
    private function generateNotes(EmployeeAttendance $attendance, $status)
    {
        $notes = [];
        
        // If late, add how many minutes
        if ($status === 'late' && $attendance->check_in_time) {
            $shift = $attendance->effective_shift;
            $date = $attendance->date->format('Y-m-d');
            
            if ($shift) {
                $workStartTime = Carbon::parse($date . ' ' . $shift->start_time);
            } else {
                $workStartTime = Carbon::parse($date . ' ' . \App\Models\AttendanceSetting::getValue('work_start_time', '08:00'));
            }
            
            if ($attendance->check_in_time->gt($workStartTime)) {
                $lateMinutes = (int) abs(round($workStartTime->floatDiffInMinutes($attendance->check_in_time)));
                if ($lateMinutes > 0) {
                    $notes[] = "Terlambat {$lateMinutes} menit";
                }
            }
        }

        // Keep existing manual notes if they don't look like auto-generated ones
        $existingNotes = $attendance->getOriginal('notes');
        if ($existingNotes) {
            // Filter out old auto-generated notes (anything starting with "Terlambat ")
            $manualNotes = collect(explode(', ', $existingNotes))
                ->filter(fn($note) => !str_starts_with($note, 'Terlambat'))
                ->filter(fn($note) => !empty(trim($note)))
                ->implode(', ');
            
            if ($manualNotes) {
                $notes[] = $manualNotes;
            }
        }
        
        return implode(', ', $notes) ?: null;
    }
}
