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
                // to ensure we identify the correct first/last tap.
                // We use whereBetween with start/end of day to handle timezone crossings (UTC vs Local)
                $startOfDay = Carbon::parse($date)->startOfDay();
                $endOfDay = Carbon::parse($date)->endOfDay();
                
                $allLogsForDay = AttendanceLog::where('user_id', $uid)
                    ->whereBetween('datetime', [$startOfDay, $endOfDay])
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
        if ($logs->isEmpty()) {
            $attendance->check_in_time = null;
            $attendance->check_out_time = null;
            return;
        }

        $shift = $attendance->effective_shift;
        $date = $attendance->date->format('Y-m-d');
        
        // Define midpoint for basic grouping (12:00 PM fallback)
        $cutoffHour = 12;

        if ($shift && $shift->start_time && $shift->end_time) {
            $start = Carbon::parse($date . ' ' . $shift->start_time);
            $end = Carbon::parse($date . ' ' . $shift->end_time);
            
            // Handle shifts crossing midnight
            if ($end->lt($start)) {
                $end->addDay();
            }
            
            $duration = $start->diffInMinutes($end);
            $midpoint = $start->copy()->addMinutes($duration / 2);
            $cutoffHour = $midpoint->hour;
        }

        $allTaps = $logs->pluck('datetime')->sort()->values();
        
        if ($allTaps->count() === 1) {
            $tap = $allTaps->first();
            
            // If shift exists, check which one it's closer to
            if ($shift && $shift->start_time && $shift->end_time) {
                $start = Carbon::parse($date . ' ' . $shift->start_time);
                $end = Carbon::parse($date . ' ' . $shift->end_time);
                
                $diffToStart = abs($tap->diffInMinutes($start));
                $diffToEnd = abs($tap->diffInMinutes($end));
                
                if ($diffToStart <= $diffToEnd) {
                    $attendance->check_in_time = $tap;
                    $attendance->check_out_time = null;
                } else {
                    $attendance->check_in_time = null;
                    $attendance->check_out_time = $tap;
                }
            } else {
                // Default fallback based on cutoff
                if ($tap->hour < $cutoffHour) {
                    $attendance->check_in_time = $tap;
                    $attendance->check_out_time = null;
                } else {
                    $attendance->check_in_time = null;
                    $attendance->check_out_time = $tap;
                }
            }
            return;
        }

        // Multiple taps: First is check-in, Last is check-out
        $firstTap = $allTaps->first();
        $lastTap = $allTaps->last();
        
        $attendance->check_in_time = $firstTap;
        
        // Rule: Check-out must be at least 30 minutes after check-in
        // This prevents accidental double-taps from being recorded as premature check-outs
        if (abs($lastTap->diffInMinutes($firstTap)) >= 30) {
            $attendance->check_out_time = $lastTap;
        } else {
            $attendance->check_out_time = null;
        }
    }

    private function determineStatus(EmployeeAttendance $attendance)
    {
        $isPastDate = $attendance->date->isPast() && !$attendance->date->isToday();
        
        // Case: No Logs at all
        if (!$attendance->check_in_time && !$attendance->check_out_time) {
            return 'absent';
        }

        // Case: Only Check-Out exists
        if (!$attendance->check_in_time && $attendance->check_out_time) {
            return 'incomplete';
        }

        // Case: Only Check-In exists
        if ($attendance->check_in_time && !$attendance->check_out_time) {
            // If it's a past date, it's incomplete because they missed checkout
            if ($isPastDate) {
                return 'incomplete';
            }
            // If it's today, it's still "on_time" (pending checkout) or "late"
            // We use the normal check-in status logic below
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
        
        // Final Status check based on Check-In time (since we have one)
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
