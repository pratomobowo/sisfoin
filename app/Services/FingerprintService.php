<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\MesinFinger;
use App\Models\User;
use App\Models\Employee\Attendance as EmployeeAttendance;
use App\Services\AttendanceService;
use App\Services\AdmsApiService;
use Illuminate\Support\Facades\Log;

class FingerprintService
{
    private $admsService;

    public function __construct()
    {
        $this->admsService = new AdmsApiService();
    }

    /**
     * Get attendance logs from ADMS API
     */
    public function getAttendanceLogs(string $startDate, string $endDate, ?string $employeeId = null)
    {
        try {
            Log::info('FingerprintService::getAttendanceLogs - Fetching data from ADMS API', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'employee_id' => $employeeId,
            ]);

            $result = $this->admsService->getAllAttendanceLogs($startDate, $endDate, $employeeId);

            if ($result['success']) {
                Log::info('FingerprintService::getAttendanceLogs - Successfully retrieved logs from ADMS', [
                    'count' => count($result['data']),
                ]);

                return $result;
            } else {
                Log::warning('FingerprintService::getAttendanceLogs - ADMS API failed', [
                    'error' => $result['message'],
                ]);

                return $result;
            }

        } catch (\Exception $e) {
            Log::error('FingerprintService::getAttendanceLogs - Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to get attendance logs: '.$e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Get user information from fingerprint_user_mappings table for autocomplete
     */
    public function getUserInfo()
    {
        try {
            Log::info('FingerprintService::getUserInfo - Getting user info from fingerprint_user_mappings table');

            $mappings = \DB::table('fingerprint_user_mappings')
                ->select('pin', 'name')
                ->orderBy('name')
                ->get()
                ->map(function ($item) {
                    return [
                        'pin' => $item->pin,
                        'name' => $item->name,
                    ];
                })
                ->toArray();

            Log::info('FingerprintService::getUserInfo - Successfully retrieved user info', [
                'count' => count($mappings),
            ]);

            return [
                'success' => true,
                'message' => 'Data retrieved from fingerprint_user_mappings table',
                'data' => $mappings,
            ];

        } catch (\Exception $e) {
            Log::error('FingerprintService::getUserInfo - Exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to get user info: '.$e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Test connection to ADMS API
     */
    public function testConnection()
    {
        try {
            Log::info('FingerprintService::testConnection - Testing connection to ADMS API');

            return $this->admsService->testConnection();

        } catch (\Exception $e) {
            Log::error('FingerprintService::testConnection - Exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Connection test failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Save attendance logs to database with direct user lookup and auto-process to employee_attendances
     */
    public function saveAttendanceLogsToDatabase($attendanceData, $autoProcessEmployeeAttendances = true)
    {
        try {
            $savedCount = 0;
            $updatedCount = 0;
            $mappedCount = 0;
            $unmappedCount = 0;
            $processedCount = 0;

            // Get all MesinFinger records to map device_sn to mesin_finger_id
            $machines = MesinFinger::all()->pluck('id', 'serial_number')->toArray();

            foreach ($attendanceData as $data) {
                // Find user by PIN from users table
                $user = User::where('fingerprint_pin', $data['pin'])
                    ->where('fingerprint_enabled', true)
                    ->first();

                // Map device_sn to mesin_finger_id
                // Since we are removing MesinFinger module, we will check if table exists or use a default
                // For now, to maintain compatibility with existing table schema which requires mesin_finger_id
                $mesinFingerId = 1; // Default ID
                
                // If we need to maintain MesinFinger table for now:
                if (isset($machines[$data['device_sn'] ?? ''])) {
                    $mesinFingerId = $machines[$data['device_sn']];
                } else if (!empty($data['device_sn'])) {
                     try {
                         // Try to create if not exists
                         $newMachine = MesinFinger::firstOrCreate(
                             ['serial_number' => $data['device_sn']],
                             [
                                 'nama_mesin' => 'ADMS Device ' . $data['device_sn'],
                                 'status' => 'active',
                                 'ip_address' => '0.0.0.0', // Dummy
                                 'port' => 0, // Dummy
                                 'lokasi' => 'ADMS Auto-Registered',
                             ]
                         );
                         $machines[$data['device_sn']] = $newMachine->id;
                         $mesinFingerId = $newMachine->id;
                     } catch (\Exception $e) {
                         // Fallback if creation fails
                         $mesinFingerId = 1;
                     }
                }

                // Prepare log data
                $logData = [
                    'adms_id' => $data['adms_id'] ?? null,
                    'pin' => $data['pin'],
                    'name' => $data['name'] ?? ($user ? $user->name : null),
                    'datetime' => $data['datetime'],
                    'status' => $data['status'],
                    'verify' => $data['verified'] ?? $data['verify'] ?? 0,
                    'workcode' => $data['workcode'] ?? 0,
                    'mesin_finger_id' => $mesinFingerId,
                    'raw_data' => $data['raw_data'] ?? null,
                ];

                // Add user information if available
                if ($user) {
                    $logData['user_id'] = $user->id;
                    $mappedCount++;
                } else {
                    $unmappedCount++;
                }

                // Check if record already exists
                $existingLog = null;

                // Priority 1: Check by adms_id (Robust against time edits)
                if (!empty($data['adms_id'])) {
                    $existingLog = AttendanceLog::where('adms_id', $data['adms_id'])->first();
                }

                // Priority 2: Check by pin + datetime (Legacy/Fallback)
                if (!$existingLog) {
                    $existingLog = AttendanceLog::where('pin', $data['pin'])
                        ->where('datetime', $data['datetime'])
                        ->first();
                }

                if ($existingLog) {
                    // Update existing record
                    $existingLog->update($logData);
                    $updatedCount++;
                } else {
                    // Create new record
                    $logData['created_at'] = now();
                    $logData['updated_at'] = now();
                    AttendanceLog::create($logData);
                    $savedCount++;
                }
            }

            Log::info('FingerprintService::saveAttendanceLogsToDatabase - Logs saved', [
                'saved' => $savedCount,
                'updated' => $updatedCount,
                'mapped' => $mappedCount,
                'unmapped' => $unmappedCount,
                'total' => count($attendanceData),
            ]);

            // Auto-process to employee_attendances if requested and we have mapped data
            if ($autoProcessEmployeeAttendances && $mappedCount > 0) {
                $processResult = $this->processToEmployeeAttendances($attendanceData);
                if ($processResult['success']) {
                    $processedCount = $processResult['processed_count'];
                }
            }

            $message = "Successfully saved $savedCount new records and updated $updatedCount existing records. Mapped: $mappedCount, Unmapped: $unmappedCount";
            if ($processedCount > 0) {
                $message .= ". Processed to employee attendances: $processedCount";
            }

            return [
                'success' => true,
                'message' => $message,
                'saved_count' => $savedCount,
                'updated_count' => $updatedCount,
                'mapped_count' => $mappedCount,
                'unmapped_count' => $unmappedCount,
                'processed_count' => $processedCount,
            ];

        } catch (\Exception $e) {
            Log::error('FingerprintService::saveAttendanceLogsToDatabase - Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to save attendance logs: '.$e->getMessage(),
            ];
        }
    }

    private function processToEmployeeAttendances($attendanceData)
    {
        try {
            $processedCount = 0;
            $attendanceService = new AttendanceService();
            
            // Get unique dates from the attendance data
            $dates = collect($attendanceData)
                ->map(function($item) {
                    try {
                        return isset($item['datetime']) ? 
                            (\Carbon\Carbon::parse($item['datetime'])->format('Y-m-d')) : null;
                    } catch (\Exception $e) {
                        return null;
                    }
                })
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            foreach ($dates as $date) {
                $result = $attendanceService->processLogs($date, $date);
                if ($result['success']) {
                    $processedCount += $result['processed_count'];
                }
            }

            Log::info('FingerprintService::processToEmployeeAttendances - Processing completed', [
                'dates_processed' => count($dates),
                'records_processed' => $processedCount,
            ]);

            return [
                'success' => true,
                'processed_count' => $processedCount,
                'dates_processed' => count($dates),
            ];

        } catch (\Exception $e) {
            Log::error('FingerprintService::processToEmployeeAttendances - Exception', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to process to employee attendances: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get user by PIN directly from users table
     */
    public function getUserByPin($pin)
    {
        return User::where('fingerprint_pin', $pin)
            ->where('fingerprint_enabled', true)
            ->first();
    }

    /**
     * Get user by PIN2 (ID2) dari fingerprint_user_mappings table
     */
     /**
     * Get fingerprint statistics
     */
    public function getFingerprintStatistics()
    {
        $totalUsers = User::where('fingerprint_enabled', true)->count();
        $usersWithPin = User::whereNotNull('fingerprint_pin')
            ->where('fingerprint_enabled', true)
            ->count();

        return [
            'total_users' => $totalUsers,
            'users_with_pin' => $usersWithPin,
            'users_without_pin' => $totalUsers - $usersWithPin,
        ];
    }
}
