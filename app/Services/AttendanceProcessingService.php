<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\Employee\Attendance as EmployeeAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceProcessingService
{
    /**
     * Process attendance logs and convert them to employee attendance records
     */
    public function processAttendanceLogs($dateFrom = null, $dateTo = null)
    {
        $query = AttendanceLog::with(['user'])
            ->whereNotNull('user_id') // Only logs with mapped users
            ->whereNull('processed_at'); // Only unprocessed logs

        if ($dateFrom) {
            $query->where('datetime', '>=', Carbon::parse($dateFrom)->startOfDay());
        }

        if ($dateTo) {
            $query->where('datetime', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        $logs = $query->orderBy('datetime')->get();

        $processedCount = 0;
        $errorCount = 0;

        foreach ($logs as $log) {
            DB::beginTransaction();

            try {
                // Group logs by user and date
                $date = $log->datetime->toDateString();
                
                // Get or create attendance record for the user and date
                $attendance = EmployeeAttendance::firstOrCreate([
                    'user_id' => $log->user_id,
                    'date' => $date,
                ]);

                // Update check-in and check-out times based on the time of day
                $time = $log->datetime->format('H:i');
                
                // Get the effective shift for the user and date
                $shift = \App\Models\EmployeeShiftAssignment::getShiftForDate($log->user_id, $log->datetime);
                if (!$shift) {
                    $shift = \App\Models\WorkShift::getDefault();
                }

                // Logic for check-in and check-out:
                // We'll use the shift's midpoint or a fixed cutoff if no shift
                $cutoffHour = 12;
                if ($shift && $shift->start_time && $shift->end_time) {
                    $start = Carbon::parse($shift->start_time);
                    $end = Carbon::parse($shift->end_time);
                    
                    // If end_time is earlier than start_time, it's an overnight shift (not supported yet but good to note)
                    if ($end->lt($start)) {
                        $end->addDay();
                    }
                    
                    $duration = $start->diffInMinutes($end);
                    $midpoint = $start->copy()->addMinutes($duration / 2);
                    $cutoffHour = $midpoint->hour;
                }

                if ($log->datetime->hour < $cutoffHour) {
                    if (!$checkInTime || $time < $checkInTime) {
                        $attendance->check_in_time = $log->datetime;
                    }
                } else {
                    if (!$checkOutTime || $time > $checkOutTime) {
                        $attendance->check_out_time = $log->datetime;
                    }
                }

                // Calculate total hours and overtime hours
                $attendance->total_hours = $attendance->calculateTotalHours();
                $attendance->overtime_hours = $attendance->calculateOvertimeHours();

                // Determine attendance status
                $attendance->status = $this->determineAttendanceStatus($attendance, $log->datetime);

                $attendance->save();

                // Mark the log as processed
                $log->update(['processed_at' => now()]);
                
                DB::commit();
                $processedCount++;
                
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Error processing attendance log: ' . $e->getMessage(), [
                    'log_id' => $log->id,
                    'error' => $e->getMessage(),
                ]);
                $errorCount++;
            }
        }

        return [
            'processed' => $processedCount,
            'errors' => $errorCount,
            'message' => "Berhasil memproses {$processedCount} data absensi, {$errorCount} gagal"
        ];
    }

    /**
     * Determine attendance status based on check-in and check-out times
     */
    private function determineAttendanceStatus($attendance, $logDateTime = null)
    {
        if (!$attendance->check_in_time) {
            return 'absent';
        }

        if ($attendance->isLate()) {
            return 'late';
        }
        
        // Check for early arrival
        $shift = $attendance->effective_shift;
        if ($shift && $shift->early_arrival_threshold) {
            $earlyThreshold = Carbon::parse($attendance->date . ' ' . $shift->early_arrival_threshold);
            if ($attendance->check_in_time->lte($earlyThreshold)) {
                return 'early_arrival';
            }
        }

        return 'on_time';
    }

    /**
     * Process attendance logs for a specific date range
     */
    public function processAttendanceLogsByDateRange($dateFrom, $dateTo)
    {
        return $this->processAttendanceLogs($dateFrom, $dateTo);
    }

    /**
     * Process attendance logs for a specific user
     */
    public function processAttendanceLogsForUser($userId, $dateFrom = null, $dateTo = null)
    {
        $query = AttendanceLog::with(['user'])
            ->where('user_id', $userId)
            ->whereNull('processed_at');

        if ($dateFrom) {
            $query->where('datetime', '>=', Carbon::parse($dateFrom)->startOfDay());
        }

        if ($dateTo) {
            $query->where('datetime', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        $logs = $query->orderBy('datetime')->get();

        $processedCount = 0;
        $errorCount = 0;

        foreach ($logs as $log) {
            DB::beginTransaction();

            try {
                $date = $log->datetime->toDateString();
                
                $attendance = EmployeeAttendance::firstOrCreate([
                    'user_id' => $log->user_id,
                    'date' => $date,
                ]);

                $time = $log->datetime->format('H:i');
                
                $checkInTime = $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : null;
                $checkOutTime = $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : null;

                $shift = \App\Models\EmployeeShiftAssignment::getShiftForDate($log->user_id, $log->datetime);
                if (!$shift) {
                    $shift = \App\Models\WorkShift::getDefault();
                }

                $cutoffHour = 12;
                if ($shift && $shift->start_time && $shift->end_time) {
                    $start = Carbon::parse($shift->start_time);
                    $end = Carbon::parse($shift->end_time);
                    if ($end->lt($start)) $end->addDay();
                    $duration = $start->diffInMinutes($end);
                    $midpoint = $start->copy()->addMinutes($duration / 2);
                    $cutoffHour = $midpoint->hour;
                }

                if ($log->datetime->hour < $cutoffHour) {
                    if (!$checkInTime || $time < $checkInTime) {
                        $attendance->check_in_time = $log->datetime;
                    }
                } else {
                    if (!$checkOutTime || $time > $checkOutTime) {
                        $attendance->check_out_time = $log->datetime;
                    }
                }

                $attendance->total_hours = $attendance->calculateTotalHours();
                $attendance->overtime_hours = $attendance->calculateOvertimeHours();
                $attendance->status = $this->determineAttendanceStatus($attendance, $log->datetime);

                $attendance->save();

                $log->update(['processed_at' => now()]);
                
                DB::commit();
                $processedCount++;
                
            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Error processing attendance log for user: ' . $e->getMessage(), [
                    'log_id' => $log->id,
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
                $errorCount++;
            }
        }

        return [
            'processed' => $processedCount,
            'errors' => $errorCount,
            'message' => "Berhasil memproses {$processedCount} data absensi untuk user {$userId}, {$errorCount} gagal"
        ];
    }
}