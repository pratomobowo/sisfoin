<?php

namespace App\Services;

use App\Models\MesinFinger;
use App\Models\User;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;
use Carbon\Carbon;

class FingerprintManagementService
{
    /**
     * Create a new fingerprint machine.
     */
    public function createMachine(array $data): MesinFinger
    {
        DB::beginTransaction();
        
        try {
            // Skip connectivity validation in test environment
            if (!app()->environment('testing') && !empty($data['ip_address'])) {
                $this->validateMachineConnectivity($data['ip_address'], $data['port'] ?? 4370);
            }

            $machine = MesinFinger::create([
                'nama_mesin' => $data['nama_mesin'],
                'ip_address' => $data['ip_address'],
                'port' => $data['port'] ?? 4370,
                'lokasi' => $data['lokasi'] ?? null,
                'keterangan' => $data['keterangan'] ?? null,
                'status' => $data['status'] ?? 'active',
                'device_model' => $data['device_model'] ?? 'X100C',
                'serial_number' => $data['serial_number'] ?? null,
            ]);

            // Log activity
            if (auth()->check()) {
                activity()
                    ->performedOn($machine)
                    ->causedBy(auth()->user())
                    ->log('Fingerprint machine created');
            }

            DB::commit();
            
            return $machine;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create fingerprint machine', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing fingerprint machine.
     */
    public function updateMachine(MesinFinger $machine, array $data): MesinFinger
    {
        DB::beginTransaction();
        
        try {
            // Validate machine connectivity if IP changed
            if (!empty($data['ip_address']) && $data['ip_address'] !== $machine->ip_address) {
                $this->validateMachineConnectivity($data['ip_address'], $data['port'] ?? $machine->port);
            }

            $machine->update([
                'name' => $data['name'],
                'ip_address' => $data['ip_address'],
                'port' => $data['port'] ?? 4370,
                'location' => $data['location'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'active',
                'device_type' => $data['device_type'] ?? 'fingerprint',
                'serial_number' => $data['serial_number'] ?? null,
                'firmware_version' => $data['firmware_version'] ?? null,
            ]);

            // Log activity
            activity()
                ->performedOn($machine)
                ->causedBy(auth()->user())
                ->log('Fingerprint machine updated');

            DB::commit();
            
            return $machine->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update fingerprint machine', [
                'machine_id' => $machine->id,
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Test connection to a fingerprint machine.
     */
    public function testConnection(MesinFinger $machine): array
    {
        try {
            $startTime = microtime(true);
            
            // Try to connect to the machine
            $response = $this->connectToMachine($machine);
            
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2);

            if ($response['success']) {
                // Update machine status
                $machine->update([
                    'status' => 'active',
                    'last_ping' => now(),
                ]);

                return [
                    'success' => true,
                    'message' => 'Koneksi berhasil',
                    'response_time' => $responseTime,
                    'device_info' => $response['device_info'] ?? null,
                ];
            } else {
                // Update machine status
                $machine->update([
                    'status' => 'error',
                    'last_ping' => now(),
                ]);

                return [
                    'success' => false,
                    'message' => $response['message'] ?? 'Koneksi gagal',
                    'response_time' => $responseTime,
                ];
            }
        } catch (Exception $e) {
            Log::error('Failed to test machine connection', [
                'machine_id' => $machine->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'response_time' => null,
            ];
        }
    }

    /**
     * Sync attendance data from a fingerprint machine.
     */
    public function syncAttendanceData(MesinFinger $machine): array
    {
        DB::beginTransaction();
        
        try {
            $connection = $this->connectToMachine($machine);
            
            if (!$connection['success']) {
                throw new Exception('Tidak dapat terhubung ke mesin: ' . $connection['message']);
            }

            // Get attendance logs from machine
            $attendanceLogs = $this->getAttendanceLogsFromMachine($machine);
            
            $syncedCount = 0;
            $errors = [];

            foreach ($attendanceLogs as $log) {
                try {
                    $this->processAttendanceLog($machine, $log);
                    $syncedCount++;
                } catch (Exception $e) {
                    $errors[] = "Log {$log['id']}: " . $e->getMessage();
                }
            }

            // Update last sync time
            $machine->update(['last_sync' => now()]);

            // Log activity
            activity()
                ->performedOn($machine)
                ->causedBy(auth()->user())
                ->withProperties([
                    'synced_count' => $syncedCount,
                    'errors_count' => count($errors)
                ])
                ->log('Attendance data synced');

            DB::commit();

            return [
                'success' => true,
                'synced_count' => $syncedCount,
                'errors' => $errors,
                'message' => "Berhasil sinkronisasi {$syncedCount} data absensi",
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to sync attendance data', [
                'machine_id' => $machine->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal sinkronisasi: ' . $e->getMessage(),
                'synced_count' => 0,
                'errors' => [],
            ];
        }
    }

    /**
     * Sync user data to fingerprint machine.
     */
    public function syncUserData(MesinFinger $machine, ?User $user = null): array
    {
        try {
            $connection = $this->connectToMachine($machine);
            
            if (!$connection['success']) {
                throw new Exception('Tidak dapat terhubung ke mesin: ' . $connection['message']);
            }

            $users = $user ? collect([$user]) : User::where('fingerprint_enabled', true)->get();
            
            $syncedCount = 0;
            $errors = [];

            foreach ($users as $userData) {
                try {
                    $this->syncUserToMachine($machine, $userData);
                    $syncedCount++;
                } catch (Exception $e) {
                    $errors[] = "User {$userData->name}: " . $e->getMessage();
                }
            }

            // Log activity
            activity()
                ->performedOn($machine)
                ->causedBy(auth()->user())
                ->withProperties([
                    'synced_count' => $syncedCount,
                    'errors_count' => count($errors)
                ])
                ->log('User data synced to machine');

            return [
                'success' => true,
                'synced_count' => $syncedCount,
                'errors' => $errors,
                'message' => "Berhasil sinkronisasi {$syncedCount} data pengguna",
            ];
        } catch (Exception $e) {
            Log::error('Failed to sync user data', [
                'machine_id' => $machine->id,
                'user_id' => $user?->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal sinkronisasi: ' . $e->getMessage(),
                'synced_count' => 0,
                'errors' => [],
            ];
        }
    }

    /**
     * Get machine status and information.
     */
    public function getMachineStatus(MesinFinger $machine): array
    {
        try {
            $connection = $this->connectToMachine($machine);
            
            if (!$connection['success']) {
                return [
                    'status' => 'offline',
                    'message' => $connection['message'],
                    'device_info' => null,
                    'user_count' => 0,
                    'log_count' => 0,
                ];
            }

            $deviceInfo = $this->getMachineInfo($machine);
            
            return [
                'status' => 'online',
                'message' => 'Mesin terhubung',
                'device_info' => $deviceInfo,
                'user_count' => $deviceInfo['user_count'] ?? 0,
                'log_count' => $deviceInfo['log_count'] ?? 0,
                'last_activity' => $deviceInfo['last_activity'] ?? null,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage(),
                'device_info' => null,
                'user_count' => 0,
                'log_count' => 0,
            ];
        }
    }

    /**
     * Clear all data from fingerprint machine.
     */
    public function clearMachineData(MesinFinger $machine, string $dataType = 'all'): array
    {
        try {
            $connection = $this->connectToMachine($machine);
            
            if (!$connection['success']) {
                throw new Exception('Tidak dapat terhubung ke mesin: ' . $connection['message']);
            }

            $result = $this->clearDataFromMachine($machine, $dataType);

            // Log activity
            activity()
                ->performedOn($machine)
                ->causedBy(auth()->user())
                ->withProperties(['data_type' => $dataType])
                ->log('Machine data cleared');

            return $result;
        } catch (Exception $e) {
            Log::error('Failed to clear machine data', [
                'machine_id' => $machine->id,
                'data_type' => $dataType,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate machine connectivity.
     */
    private function validateMachineConnectivity(string $ipAddress, int $port): void
    {
        $connection = @fsockopen($ipAddress, $port, $errno, $errstr, 5);
        
        if (!$connection) {
            throw new Exception("Tidak dapat terhubung ke {$ipAddress}:{$port} - {$errstr}");
        }
        
        fclose($connection);
    }

    /**
     * Connect to fingerprint machine.
     */
    private function connectToMachine(MesinFinger $machine): array
    {
        // This is a placeholder for actual machine connection logic
        // Implementation depends on the specific fingerprint device SDK/API
        
        try {
            // Simulate connection attempt
            $connection = @fsockopen($machine->ip_address, $machine->port, $errno, $errstr, 5);
            
            if (!$connection) {
                return [
                    'success' => false,
                    'message' => "Connection failed: {$errstr}",
                ];
            }
            
            fclose($connection);
            
            return [
                'success' => true,
                'message' => 'Connected successfully',
                'device_info' => [
                    'model' => 'Unknown',
                    'firmware' => 'Unknown',
                    'serial' => 'Unknown',
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get attendance logs from machine.
     */
    private function getAttendanceLogsFromMachine(MesinFinger $machine): array
    {
        // Placeholder for actual implementation
        // This would use the specific SDK/API for the fingerprint device
        return [];
    }

    /**
     * Process attendance log from machine.
     */
    private function processAttendanceLog(MesinFinger $machine, array $logData): void
    {
        // Find user by fingerprint PIN or employee ID
        $user = User::where('fingerprint_pin', $logData['user_id'])
                   ->orWhere('employee_id', $logData['user_id'])
                   ->first();

        AttendanceLog::updateOrCreate([
            'machine_id' => $machine->id,
            'user_id' => $user?->id,
            'datetime' => Carbon::parse($logData['datetime']),
        ], [
            'fingerprint_pin' => $logData['user_id'],
            'verify_type' => $logData['verify_type'] ?? 1,
            'in_out_mode' => $logData['in_out_mode'] ?? 0,
            'work_code' => $logData['work_code'] ?? 0,
        ]);
    }

    /**
     * Sync user to machine.
     */
    private function syncUserToMachine(MesinFinger $machine, User $user): void
    {
        // Placeholder for actual implementation
        // This would use the specific SDK/API to upload user data to the device
    }

    /**
     * Get machine information.
     */
    private function getMachineInfo(MesinFinger $machine): array
    {
        // Placeholder for actual implementation
        return [
            'model' => 'Unknown',
            'firmware' => 'Unknown',
            'serial' => 'Unknown',
            'user_count' => 0,
            'log_count' => 0,
            'last_activity' => null,
        ];
    }

    /**
     * Clear data from machine.
     */
    private function clearDataFromMachine(MesinFinger $machine, string $dataType): array
    {
        // Placeholder for actual implementation
        return [
            'success' => true,
            'message' => "Data {$dataType} berhasil dihapus dari mesin",
        ];
    }
}