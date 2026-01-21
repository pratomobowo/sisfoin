<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\Employee\Attendance as EmployeeAttendance;
use App\Models\User;
use App\Models\FingerprintUserMapping;
use App\Exceptions\AttendanceProcessingException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendancePreprocessingService
{
    protected AttendanceSettingsService $settingsService;
    protected ShiftResolverService $shiftResolver;

    public function __construct()
    {
        $this->settingsService = new AttendanceSettingsService();
        $this->shiftResolver = new ShiftResolverService();
    }

    /**
     * Process all attendance logs from attendance_log table to employee_attendance table
     * This will process ALL data without exception
     */
    public function processAllAttendanceLogs()
    {
        $startTime = microtime(true);
        $processedCount = 0;
        $errorCount = 0;
        $skippedCount = 0;

        Log::info('Starting attendance logs preprocessing', [
            'start_time' => $startTime,
            'user_id' => auth()->id(),
        ]);

        try {
            // Get all attendance logs, ordered by datetime
            $logs = AttendanceLog::orderBy('datetime')->get();
            
            Log::info('Retrieved attendance logs for processing', [
                'total_logs' => $logs->count(),
            ]);

            // Group logs by user and date for processing
            $groupedLogs = $logs->groupBy(function($log) {
                return $log->user_id . '_' . $log->datetime->format('Y-m-d');
            });

            Log::info('Grouped logs by user and date', [
                'total_groups' => $groupedLogs->count(),
            ]);

            DB::beginTransaction();

            try {
                foreach ($groupedLogs as $groupKey => $userLogs) {
                    $firstLog = $userLogs->first();
                    
                    // Skip if no user_id mapping
                    if (!$firstLog->user_id) {
                        $skippedCount += $userLogs->count();
                        Log::debug('Skipping logs without user mapping', [
                            'group_key' => $groupKey,
                            'log_count' => $userLogs->count(),
                        ]);
                        continue;
                    }

                    $date = $firstLog->datetime->format('Y-m-d');
                    $userId = $firstLog->user_id;

                    try {
                        // Delete existing attendance record for this user and date
                        // This ensures we always have the latest processed data
                        $deleted = EmployeeAttendance::where('user_id', $userId)
                            ->where('date', $date)
                            ->delete();

                        Log::debug('Deleted existing attendance records', [
                            'user_id' => $userId,
                            'date' => $date,
                            'deleted_count' => $deleted,
                        ]);

                        // Process the logs to determine check-in and check-out times
                        $processedData = $this->processUserLogsForDate($userLogs, $userId);

                        // Create new attendance record
                        EmployeeAttendance::create([
                            'user_id' => $userId,
                            'date' => $date,
                            'check_in_time' => $processedData['check_in_time'],
                            'check_out_time' => $processedData['check_out_time'],
                            'total_hours' => $processedData['total_hours'],
                            'overtime_hours' => $processedData['overtime_hours'],
                            'status' => $processedData['status'],
                            'notes' => $processedData['notes'],
                            'created_by' => auth()->id(),
                        ]);

                        $processedCount++;

                        Log::debug('Successfully processed attendance group', [
                            'user_id' => $userId,
                            'date' => $date,
                            'processed_count' => $processedCount,
                        ]);

                    } catch (\Exception $e) {
                        $errorCount++;
                        Log::error('Error processing attendance group', [
                            'user_id' => $userId,
                            'date' => $date,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        
                        // Continue processing other groups
                        continue;
                    }
                }

                DB::commit();

                $endTime = microtime(true);
                $executionTime = round($endTime - $startTime, 2);

                Log::info('Completed attendance logs preprocessing', [
                    'processed' => $processedCount,
                    'errors' => $errorCount,
                    'skipped' => $skippedCount,
                    'execution_time' => $executionTime,
                    'end_time' => $endTime,
                ]);

                return [
                    'processed' => $processedCount,
                    'errors' => $errorCount,
                    'skipped' => $skippedCount,
                    'execution_time' => $executionTime,
                    'message' => "Berhasil memproses {$processedCount} data absensi, {$skippedCount} dilewati (tidak ada mapping user), {$errorCount} gagal. Waktu eksekusi: {$executionTime} detik"
                ];

            } catch (\Exception $e) {
                DB::rollback();
                
                Log::error('Database transaction failed during attendance processing', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'processed_so_far' => $processedCount,
                ]);

                throw new AttendanceProcessingException(
                    'Gagal memproses data absensi karena error database: ' . $e->getMessage(),
                    0,
                    $e,
                    [
                        'processed_so_far' => $processedCount,
                        'errors_so_far' => $errorCount,
                        'skipped_so_far' => $skippedCount,
                    ]
                );
            }

        } catch (\Exception $e) {
            $endTime = microtime(true);
            $executionTime = round($endTime - $startTime, 2);
            
            Log::error('Critical error during attendance preprocessing', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'execution_time' => $executionTime,
                'processed' => $processedCount,
                'errors' => $errorCount,
                'skipped' => $skippedCount,
            ]);

            if (!$e instanceof AttendanceProcessingException) {
                throw new AttendanceProcessingException(
                    'Error kritis saat memproses data absensi: ' . $e->getMessage(),
                    0,
                    $e,
                    [
                        'execution_time' => $executionTime,
                        'processed' => $processedCount,
                        'errors' => $errorCount,
                        'skipped' => $skippedCount,
                    ]
                );
            }

            throw $e;
        }
    }

    /**
     * Process logs for a specific user and date to determine attendance data
     */
    /**
     * Process logs for a specific user and date to determine attendance data
     */
    private function processUserLogsForDate($userLogs, $userId = null)
    {
        $checkInTime = null;
        $checkOutTime = null;
        $notes = [];
        
        // Sort logs by time ascending
        $sortedLogs = $userLogs->sortBy('datetime')->values();
        $logCount = $sortedLogs->count();

        if ($logCount > 0) {
            // Logic 1: First scan is ALWAYS Check-In
            $checkInTime = $sortedLogs->first()->datetime;

            // Logic 2: Last scan is POTENTIALLY Check-Out
            if ($logCount > 1) {
                $lastLog = $sortedLogs->last()->datetime;
                
                // Logic 3: Minimum Duration Threshold (e.g., 30 minutes)
                // If the last scan is very close to the first scan, it's likely a double-tap error or just checking usage
                // rather than a valid work day checkout. 
                $minutesDiff = $checkInTime->diffInMinutes($lastLog);
                
                if ($minutesDiff >= 30) {
                    $checkOutTime = $lastLog;
                } else {
                    $notes[] = "Ignored checkout at {$lastLog->format('H:i')} (duration < 30 mins)";
                }
            }
        }

        // Calculate total hours
        $totalHours = 0;
        $overtimeHours = 0;
        
        if ($checkInTime && $checkOutTime) {
            $totalMinutes = $checkOutTime->diffInMinutes($checkInTime);
            $totalHours = round($totalMinutes / 60, 2);
            
            // Calculate overtime (hours beyond 6 working hours: 08:00-14:00)
            $overtimeHours = $totalHours > 6 ? $totalHours - 6 : 0;
        }

        // Determine attendance status
        $status = $this->determineAttendanceStatus($checkInTime, $checkOutTime, $totalHours, $userId);

        // Generate additional notes
        $generatedNotes = $this->generateNotes($status, $checkInTime, $checkOutTime, $logCount);
        if ($generatedNotes) {
            $notes[] = $generatedNotes;
        }

        return [
            'check_in_time' => $checkInTime,
            'check_out_time' => $checkOutTime,
            'total_hours' => $totalHours,
            'overtime_hours' => $overtimeHours,
            'status' => $status,
            'notes' => !empty($notes) ? implode(', ', $notes) : null,
        ];
    }

    /**
     * Determine attendance status based on check-in/out times and resolved shift
     */
    private function determineAttendanceStatus($checkInTime, $checkOutTime, $totalHours, $userId = null)
    {
        if (!$checkInTime && !$checkOutTime) {
            return 'absent';
        }

        // Resolve shift for user (if userId provided) or use global settings fallback
        if ($checkInTime && $userId) {
            $date = Carbon::parse($checkInTime->format('Y-m-d'));
            $shift = $this->shiftResolver->resolveShiftForUserId($userId, $date);
            
            if ($shift) {
                return $shift->determineStatus($checkInTime);
            }
        }

        // Fallback to global settings if no shift resolved
        $earlyArrivalThreshold = $this->settingsService->getEarlyArrivalThreshold();
        $lateThreshold = $this->settingsService->getLateThreshold();

        if ($checkInTime && $checkOutTime) {
            $dateStr = $checkInTime->format('Y-m-d');
            
            // Define time thresholds from settings
            $earlyArrivalLimit = Carbon::parse($dateStr . ' ' . $earlyArrivalThreshold . ':00');
            $lateLimit = Carbon::parse($dateStr . ' ' . $lateThreshold . ':00');

            // Check Check-in Time against thresholds
            if ($checkInTime->lte($earlyArrivalLimit)) {
                return 'early_arrival';
            } elseif ($checkInTime->gt($lateLimit)) {
                return 'late';
            } else {
                return 'on_time';
            }
        }

        // Only check-in (no check-out yet)
        if ($checkInTime) {
            $dateStr = $checkInTime->format('Y-m-d');
            $earlyArrivalLimit = Carbon::parse($dateStr . ' ' . $earlyArrivalThreshold . ':00');
            $lateLimit = Carbon::parse($dateStr . ' ' . $lateThreshold . ':00');
             
            if ($checkInTime->lte($earlyArrivalLimit)) {
                return 'early_arrival';
            } elseif ($checkInTime->gt($lateLimit)) {
                return 'late';
            } else {
                return 'on_time';
            }
        }
        
        return 'absent';
    }

    /**
     * Generate notes based on attendance data
     */
    private function generateNotes($status, $checkInTime, $checkOutTime, $logCount)
    {
        $notes = [];

        if ($status === 'late' && $checkInTime) {
            $standardCheckIn = Carbon::parse($checkInTime->format('Y-m-d') . ' 08:00:00');
            // Reset standard checkin without the added minutes for diff calculation
             $baseCheckIn = Carbon::parse($checkInTime->format('Y-m-d') . ' 08:00:00');
            
            if ($checkInTime->gt($baseCheckIn->copy()->addMinutes(5))) {
                $lateMinutes = $checkInTime->diffInMinutes($baseCheckIn);
                $notes[] = "Terlambat {$lateMinutes} menit";
            }
        }

        if ($logCount > 2) {
            $notes[] = "Multiple scans: {$logCount} kali";
        }

        return implode(', ', $notes) ?: null;
    }

    /**
     * Get statistics about the attendance logs
     */
    public function getAttendanceLogStats()
    {
        $totalLogs = AttendanceLog::count();
        $mappedLogs = AttendanceLog::whereNotNull('user_id')->count();
        $unmappedLogs = AttendanceLog::whereNull('user_id')->count();
        $processedLogs = AttendanceLog::whereNotNull('processed_at')->count();
        
        $uniqueUsers = AttendanceLog::whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $dateRange = AttendanceLog::selectRaw('MIN(datetime) as min_date, MAX(datetime) as max_date')->first();

        return [
            'total_logs' => $totalLogs,
            'mapped_logs' => $mappedLogs,
            'unmapped_logs' => $unmappedLogs,
            'processed_logs' => $processedLogs,
            'unique_users' => $uniqueUsers,
            'date_range' => [
                'start' => $dateRange->min_date,
                'end' => $dateRange->max_date,
            ],
        ];
    }

    /**
     * Clear all employee attendance data (for fresh processing)
     */
    public function clearAllEmployeeAttendance()
    {
        try {
            $deletedCount = EmployeeAttendance::count();
            
            Log::info('Starting to clear all employee attendance data', [
                'total_records' => $deletedCount,
                'user_id' => auth()->id(),
            ]);

            EmployeeAttendance::truncate();
            
            Log::info('Successfully cleared all employee attendance data', [
                'deleted_count' => $deletedCount,
                'user_id' => auth()->id(),
            ]);
        
            return [
                'deleted' => $deletedCount,
                'message' => "Berhasil menghapus {$deletedCount} data employee attendance"
            ];
            
        } catch (\Exception $e) {
            Log::error('Error clearing employee attendance data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw new AttendanceProcessingException(
                'Gagal menghapus data employee attendance: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }
}
