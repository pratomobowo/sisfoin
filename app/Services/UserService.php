<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;
use Exception;

class UserService
{
    /**
     * Get all users with pagination and filtering
     */
    public function getUsers(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = User::with('roles');

        // Search filter
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                  ->orWhere('email', 'like', '%'.$filters['search'].'%')
                  ->orWhere('nip', 'like', '%'.$filters['search'].'%')
                  ->orWhere('employee_id', 'like', '%'.$filters['search'].'%');
            });
        }

        // Role filter
        if (!empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('roles.id', $filters['role']);
            });
        }

        // Employee type filter
        if (!empty($filters['employee_type'])) {
            $query->where('employee_type', $filters['employee_type']);
        }

        // Fingerprint enabled filter
        if (isset($filters['fingerprint_enabled']) && $filters['fingerprint_enabled'] !== '') {
            $query->where('fingerprint_enabled', $filters['fingerprint_enabled']);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get user by ID with relationships
     */
    public function getUserById(int $id): ?User
    {
        return User::with(['roles', 'employee', 'dosen'])->findOrFail($id);
    }

    /**
     * Create new user with validation and role assignment
     */
    public function createUser(array $data): User
    {
        DB::beginTransaction();
        
        try {
            // Validate unique constraints
            $this->validateUniqueFields($data);
            
            // Apply naming convention for specific fields to match Model
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'nip' => $data['nip'] ?? null,
                'employee_type' => $data['employee_type'] ?? null,
                'employee_id' => $data['employee_id'] ?? null,
                'fingerprint_enabled' => $data['fingerprint_enabled'] ?? false,
                'fingerprint_pin' => $data['fingerprint_pin'] ?? null,
            ];

            // Create user
            $user = User::create($userData);

            // Assign roles
            if (!empty($data['roles'])) {
                $roles = is_array($data['roles']) ? $data['roles'] : [$data['roles']];
                $user->assignRole($roles);
            }

            // Log activity
            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->log('User created');

            DB::commit();
            
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create user', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update existing user
     */
    public function updateUser(User|int $user, array $data): User
    {
        if (is_int($user)) {
            $user = User::findOrFail($user);
        }

        DB::beginTransaction();
        
        try {
            // Validate unique constraints (excluding current user)
            $this->validateUniqueFields($data, $user->id);
            
            // Prepare update data
            $updateData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'nip' => $data['nip'] ?? null,
                'employee_type' => $data['employee_type'] ?? null,
                'employee_id' => $data['employee_id'] ?? null,
                'fingerprint_enabled' => $data['fingerprint_enabled'] ?? false,
                'fingerprint_pin' => $data['fingerprint_pin'] ?? null,
            ];

            // Update password if provided
            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            // Update user
            $user->update($updateData);

            // Update roles if provided
            if (isset($data['roles'])) {
                $roles = is_array($data['roles']) ? $data['roles'] : [$data['roles']];
                $user->syncRoles($roles);
            }

            // Log activity
            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->log('User updated');

            DB::commit();
            
            return $user->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Delete user
     */
    public function deleteUser(User|int $user): bool
    {
        if (is_int($user)) {
            $user = User::findOrFail($user);
        }

        // Prevent deletion of self
        if (auth()->id() === $user->id) {
            throw ValidationException::withMessages([
                'user' => 'Anda tidak dapat menghapus akun Anda sendiri.'
            ]);
        }

        DB::beginTransaction();
        
        try {
            // Log activity before deletion
            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->withProperties(['deleted_user' => $user->toArray()])
                ->log('User deleted');

            // Delete user
            $deleted = $user->delete();

            DB::commit();
            
            return $deleted;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete user', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get all available roles
     */
    public function getAllRoles(): \Illuminate\Database\Eloquent\Collection
    {
        return Role::orderBy('name')->get();
    }
    
    /**
     * Alias for getAllRoles to maintain compatibility if needed, 
     * or prefer getAllRoles()
     */
    public function getAvailableRoles(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->getAllRoles();
    }

    /**
     * Get user roles
     */
    public function getUserRoles(int $id): \Illuminate\Database\Eloquent\Collection
    {
        $user = User::findOrFail($id);
        return $user->roles;
    }

    /**
     * Assign roles to user
     */
    public function assignRoles(int $id, array $roles): User
    {
        $user = User::findOrFail($id);
        $user->syncRoles($roles);

        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->withProperties(['roles' => $roles])
            ->log('Roles assigned to user');

        return $user->fresh();
    }

    /**
     * Validate unique fields for user.
     */
    private function validateUniqueFields(array $data, ?int $excludeUserId = null): void
    {
        $errors = [];

        // Check email uniqueness
        if (!empty($data['email'])) {
            $emailQuery = User::where('email', $data['email']);
            if ($excludeUserId) {
                $emailQuery->where('id', '!=', $excludeUserId);
            }
            if ($emailQuery->exists()) {
                $errors['email'] = 'Email sudah digunakan oleh pengguna lain.';
            }
        }

        // Check NIP uniqueness
        if (!empty($data['nip'])) {
            $nipQuery = User::where('nip', $data['nip']);
            if ($excludeUserId) {
                $nipQuery->where('id', '!=', $excludeUserId);
            }
            if ($nipQuery->exists()) {
                $errors['nip'] = 'NIP sudah digunakan oleh pengguna lain.';
            }
        }

        // Check employee_id uniqueness
        if (!empty($data['employee_id'])) {
            $employeeIdQuery = User::where('employee_id', $data['employee_id']);
            if ($excludeUserId) {
                $employeeIdQuery->where('id', '!=', $excludeUserId);
            }
            if ($employeeIdQuery->exists()) {
                $errors['employee_id'] = 'ID Karyawan sudah digunakan oleh pengguna lain.';
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Reset user password.
     */
    public function resetUserPassword(User $user, string $newPassword): User
    {
        DB::beginTransaction();
        
        try {
            $user->update([
                'password' => Hash::make($newPassword)
            ]);

            // Log activity
            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->log('User password reset');

            DB::commit();
            
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to reset user password', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Enable fingerprint access for user
     */
    public function enableFingerprintAccess(int $id, ?string $pin = null): User
    {
        $user = User::findOrFail($id);
        $user->enableFingerprintAccess($pin);

        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->withProperties(['pin_set' => !empty($pin)])
            ->log('Fingerprint access enabled');

        return $user->fresh();
    }

    /**
     * Disable fingerprint access for user
     */
    public function disableFingerprintAccess(int $id): User
    {
        $user = User::findOrFail($id);
        $user->disableFingerprintAccess();

        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->log('Fingerprint access disabled');

        return $user->fresh();
    }

    /**
     * Set fingerprint PIN for user
     */
    public function setFingerprintPin(int $id, string $pin): User
    {
        $user = User::findOrFail($id);
        $user->setFingerprintPin($pin);

        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->log('Fingerprint PIN updated');

        return $user->fresh();
    }

    /**
     * Get users with fingerprint enabled
     */
    public function getUsersWithFingerprintEnabled(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = User::fingerprintEnabled()->withFingerprintPin();

        if (!empty($filters['employee_type'])) {
            $query->where('employee_type', $filters['employee_type']);
        }

        return $query->get();
    }

    /**
     * Find user by fingerprint PIN
     */
    public function findUserByFingerprintPin(string $pin): ?User
    {
        return User::byFingerprintPin($pin)
            ->where('fingerprint_enabled', true)
            ->first();
    }

    /**
     * Get user statistics
     */
    public function getUserStatistics(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::whereNotNull('email_verified_at')->count(),
            'users_with_fingerprint' => User::fingerprintEnabled()->count(),
            'users_with_pin' => User::withFingerprintPin()->count(),
            'users_by_role' => Role::withCount('users')->get()->pluck('users_count', 'name'),
            'users_by_employee_type' => User::select('employee_type')
                ->selectRaw('count(*) as count')
                ->groupBy('employee_type')
                ->pluck('count', 'employee_type'),
        ];
    }

    /**
     * Bulk delete users
     */
    public function bulkDeleteUsers(array $userIds): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'errors' => [],
        ];

        // Filter out current user
        $userIds = array_diff($userIds, [auth()->id()]);

        foreach ($userIds as $userId) {
            try {
                if ($this->deleteUser($userId)) {
                    $results['success'][] = $userId;
                }
            } catch (Exception $e) {
                $results['failed'][] = $userId;
                $results['errors'][$userId] = $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Bulk assign roles to users
     */
    public function bulkAssignRoles(array $userIds, array $roles): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'errors' => [],
        ];

        foreach ($userIds as $userId) {
            try {
                $this->assignRoles($userId, $roles);
                $results['success'][] = $userId;
            } catch (Exception $e) {
                $results['failed'][] = $userId;
                $results['errors'][$userId] = $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Search users by multiple criteria
     */
    public function searchUsers(string $query, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        return User::where(function ($q) use ($query) {
            $q->where('name', 'like', '%'.$query.'%')
              ->orWhere('email', 'like', '%'.$query.'%')
              ->orWhere('nip', 'like', '%'.$query.'%');
        })
        ->when(!empty($filters['employee_type']), function ($q) use ($filters) {
            $q->where('employee_type', $filters['employee_type']);
        })
        ->when(!empty($filters['role']), function ($q) use ($filters) {
            $q->whereHas('roles', function ($roleQuery) use ($filters) {
                $roleQuery->where('roles.id', $filters['role']);
            });
        })
        ->with('roles')
        ->limit(50)
        ->get();
    }
}
