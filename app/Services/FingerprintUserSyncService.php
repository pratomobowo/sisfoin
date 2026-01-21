<?php

namespace App\Services;

use App\Models\FingerprintUserMapping;
use App\Models\MesinFinger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FingerprintUserSyncService
{
    /**
     * Sync users from fingerprint machine to database.
     *
     * @param  int  $machineId
     * @return array
     */
    public function syncUsersFromMachine($machineId)
    {
        $results = [
            'success' => false,
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => [],
            'message' => '',
        ];

        try {
            // Get machine
            $machine = MesinFinger::findOrFail($machineId);
            
            // Get users from machine
            $fingerprintService = new FingerprintService($machineId);
            $machineUsers = $fingerprintService->getUsersFromMachine();
            
            if (!$machineUsers['success']) {
                $results['message'] = 'Gagal mengambil data dari mesin: ' . $machineUsers['message'];
                return $results;
            }

            $usersData = $machineUsers['data'] ?? [];
            
            if (empty($usersData)) {
                $results['message'] = 'Tidak ada data user di mesin';
                $results['success'] = true;
                return $results;
            }

            // Begin transaction
            DB::beginTransaction();
            
            try {
                foreach ($usersData as $userData) {
                    $this->syncSingleUser($userData, $results);
                }
                
                DB::commit();
                $results['success'] = true;
                $results['message'] = "Sync berhasil: {$results['created']} dibuat, {$results['updated']} diupdate";
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            $results['message'] = 'Terjadi kesalahan: ' . $e->getMessage();
            $results['errors'][] = $e->getMessage();
            Log::error('Fingerprint sync error: ' . $e->getMessage(), [
                'machine_id' => $machineId,
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $results;
    }

    /**
     * Sync a single user from machine data.
     *
     * @param  array  $userData
     * @param  array  &$results
     * @return void
     */
    protected function syncSingleUser(array $userData, &$results)
    {
        try {
            // Gunakan PIN2 (ID2) sebagai pin utama, fallback ke PIN biasa jika PIN2 tidak ada
            $pin = $userData['pin2'] ?? $userData['pin'] ?? null;
            $name = $userData['name'] ?? 'Unknown';
            $pinBiasa = $userData['pin'] ?? null; // Simpan PIN biasa untuk referensi
            
            if (!$pin) {
                $results['failed']++;
                $results['errors'][] = 'User tanpa PIN2/PIN: ' . json_encode($userData);
                return;
            }

            // Check if user exists by PIN2 (sekarang menjadi pin utama)
            $existingUser = FingerprintUserMapping::findByPin($pin);
            
            if ($existingUser) {
                // Update existing user
                $existingUser->update([
                    'name' => $name,
                    'is_active' => true,
                ]);
                $results['updated']++;
            } else {
                // Create new user dengan PIN2 sebagai pin utama
                FingerprintUserMapping::create([
                    'pin' => $pin,
                    'name' => $name,
                    'is_active' => true,
                ]);
                $results['created']++;
            }

        } catch (\Exception $e) {
            $results['failed']++;
            $results['errors'][] = [
                'pin' => $userData['pin'] ?? 'unknown',
                'pin2' => $userData['pin2'] ?? 'unknown',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get users from database with filtering and pagination.
     *
     * @param  array  $filters
     * @param  int  $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getUsersFromDatabase($filters = [], $perPage = 10)
    {
        $query = FingerprintUserMapping::query();

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('pin', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Order by latest
        $query->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Create a new fingerprint user.
     *
     * @param  array  $data
     * @return \App\Models\FingerprintUserMapping
     */
    public function createUser(array $data)
    {
        return FingerprintUserMapping::create([
            'pin' => $data['pin'],
            'name' => $data['name'],
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update a fingerprint user.
     *
     * @param  int  $id
     * @param  array  $data
     * @return \App\Models\FingerprintUserMapping
     */
    public function updateUser($id, array $data)
    {
        $user = FingerprintUserMapping::findOrFail($id);
        $user->update($data);
        return $user;
    }

    /**
     * Delete a fingerprint user.
     *
     * @param  int  $id
     * @return bool
     */
    public function deleteUser($id)
    {
        $user = FingerprintUserMapping::findOrFail($id);
        return $user->delete();
    }

    /**
     * Toggle user active status.
     *
     * @param  int  $id
     * @return \App\Models\FingerprintUserMapping
     */
    public function toggleUserStatus($id)
    {
        $user = FingerprintUserMapping::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();
        return $user;
    }

    /**
     * Get sync statistics.
     *
     * @return array
     */
    public function getSyncStatistics()
    {
        $totalUsers = FingerprintUserMapping::count();
        $activeUsers = FingerprintUserMapping::active()->count();
        $inactiveUsers = FingerprintUserMapping::inactive()->count();

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'inactive_users' => $inactiveUsers,
            'sync_rate' => $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 2) : 0,
        ];
    }

    /**
     * Clean up inactive users that haven't been synced in a while.
     *
     * @param  int  $days
     * @return int
     */
    public function cleanupOldUsers($days = 30)
    {
        $cutoffDate = now()->subDays($days);
        
        return FingerprintUserMapping::where('updated_at', '<', $cutoffDate)
                                   ->where('is_active', false)
                                   ->delete();
    }
}
