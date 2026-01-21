<?php

namespace App\Services;

use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class RoleService
{
    /**
     * Get all roles with pagination and filtering
     */
    public function getRoles(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Role::with(['permissions', 'users']);

        // Search filter
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                  ->orWhere('display_name', 'like', '%'.$filters['search'].'%')
                  ->orWhere('description', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get role by ID with relationships
     */
    public function getRoleById(int $id): ?Role
    {
        return Role::with(['permissions', 'users'])->findOrFail($id);
    }

    /**
     * Create new role
     */
    public function createRole(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create([
                'name' => $data['name'],
                'display_name' => $data['display_name'] ?? $data['name'],
                'description' => $data['description'] ?? null,
                'guard_name' => 'web',
            ]);

            // Sync permissions if provided
            if (!empty($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            // Sync permissions by modules if provided
            if (!empty($data['modules'])) {
                $this->syncRolePermissionsByModules($role, $data['modules']);
            }

            // Log activity
            Log::info('Role created', [
                'role_id' => $role->id,
                'name' => $role->name,
                'created_by' => auth()->id(),
            ]);

            return $role;
        });
    }

    /**
     * Update existing role
     */
    public function updateRole(int $id, array $data): Role
    {
        return DB::transaction(function () use ($id, $data) {
            $role = Role::findOrFail($id);

            // Prevent editing super-admin role
            if ($role->name === 'super-admin') {
                throw new \Exception('Super Admin role cannot be edited');
            }

            $role->update([
                'name' => $data['name'],
                'display_name' => $data['display_name'] ?? $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            // Sync permissions if provided
            if (isset($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            // Sync permissions by modules if provided
            if (isset($data['modules'])) {
                $this->syncRolePermissionsByModules($role, $data['modules']);
            }

            // Log activity
            Log::info('Role updated', [
                'role_id' => $role->id,
                'name' => $role->name,
                'updated_by' => auth()->id(),
            ]);

            return $role->fresh();
        });
    }

    /**
     * Delete role
     */
    public function deleteRole(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $role = Role::findOrFail($id);

            // Prevent deleting super-admin role
            if ($role->name === 'super-admin') {
                throw new \Exception('Super Admin role cannot be deleted');
            }

            // Check if role has users
            if ($role->users()->count() > 0) {
                throw new \Exception('Role cannot be deleted because it has users');
            }

            $roleName = $role->name;
            $roleId = $role->id;

            $role->delete();

            // Log activity
            Log::info('Role deleted', [
                'role_id' => $roleId,
                'name' => $roleName,
                'deleted_by' => auth()->id(),
            ]);

            return true;
        });
    }

    /**
     * Get all available permissions
     */
    public function getAllPermissions(): Collection
    {
        return Permission::all();
    }

    /**
     * Get permissions grouped by module
     */
    public function getPermissionsGroupedByModule(): Collection
    {
        return Permission::all()->groupBy(function ($permission) {
            $parts = explode('.', $permission->name);
            return $parts[0] ?? 'other';
        });
    }

    /**
     * Get role permissions
     */
    public function getRolePermissions(int $id): Collection
    {
        $role = Role::findOrFail($id);
        return $role->permissions;
    }

    /**
     * Assign permissions to role
     */
    public function assignPermissions(int $id, array $permissions): Role
    {
        $role = Role::findOrFail($id);
        $role->syncPermissions($permissions);

        Log::info('Permissions assigned to role', [
            'role_id' => $role->id,
            'permissions' => $permissions,
            'assigned_by' => auth()->id(),
        ]);

        return $role->fresh();
    }

    /**
     * Get available modules configuration
     */
    public function getAvailableModules(): array
    {
        $configPath = storage_path('app/modules_config.json');
        if (file_exists($configPath)) {
            return json_decode(file_get_contents($configPath), true);
        }

        // Fallback configuration
        return [
            'user_management' => ['label' => 'Manajemen Pengguna'],
            'role_management' => ['label' => 'Manajemen Peran'],
            'employee_management' => ['label' => 'Manajemen Karyawan'],
            'dosen_management' => ['label' => 'Manajemen Dosen'],
            'payroll_management' => ['label' => 'Manajemen Slip Gaji'],
            'profile_management' => ['label' => 'Manajemen Profil'],
            'sarpras_management' => ['label' => 'Manajemen Sarana Prasarana'],
            'sekretariat_management' => ['label' => 'Manajemen Sekretariat'],
        ];
    }

    /**
     * Sync role permissions by modules
     */
    private function syncRolePermissionsByModules(Role $role, array $modules): void
    {
        $availableModules = $this->getAvailableModules();
        $permissions = [];

        foreach ($modules as $moduleKey) {
            if (isset($availableModules[$moduleKey]['permissions'])) {
                $modulePermissions = array_keys($availableModules[$moduleKey]['permissions']);
                $permissions = array_merge($permissions, $modulePermissions);
            }
        }

        // Special handling for staff role - only view permissions + profile.edit
        if ($role->name === 'staff') {
            $staffPermissions = array_filter($permissions, function ($permission) {
                return strpos($permission, '.view') !== false || $permission === 'profile.edit';
            });
            $permissions = array_unique($staffPermissions);
        }

        $role->syncPermissions($permissions);

        Log::info('Role permissions synced by modules', [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'modules' => $modules,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Get role modules
     */
    public function getRoleModules(int $id): array
    {
        $role = Role::findOrFail($id);
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $availableModules = $this->getAvailableModules();
        $selectedModules = [];

        foreach ($availableModules as $moduleKey => $moduleData) {
            if (isset($moduleData['permissions'])) {
                $modulePermissions = array_keys($moduleData['permissions']);
                // Check if role has any permission from this module
                if (array_intersect($modulePermissions, $rolePermissions)) {
                    $selectedModules[] = $moduleKey;
                }
            }
        }

        return $selectedModules;
    }

    /**
     * Get role statistics
     */
    public function getRoleStatistics(): array
    {
        return [
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'roles_with_users' => Role::has('users')->count(),
            'most_used_role' => Role::withCount('users')->orderBy('users_count', 'desc')->first(),
            'permissions_by_module' => $this->getPermissionsGroupedByModule()->map->count(),
        ];
    }

    /**
     * Bulk delete roles
     */
    public function bulkDeleteRoles(array $roleIds): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'errors' => [],
        ];

        foreach ($roleIds as $roleId) {
            try {
                if ($this->deleteRole($roleId)) {
                    $results['success'][] = $roleId;
                }
            } catch (\Exception $e) {
                $results['failed'][] = $roleId;
                $results['errors'][$roleId] = $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Bulk assign permissions to roles
     */
    public function bulkAssignPermissions(array $roleIds, array $permissions): array
    {
        $results = [
            'success' => [],
            'failed' => [],
            'errors' => [],
        ];

        foreach ($roleIds as $roleId) {
            try {
                $this->assignPermissions($roleId, $permissions);
                $results['success'][] = $roleId;
            } catch (\Exception $e) {
                $results['failed'][] = $roleId;
                $results['errors'][$roleId] = $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Search roles by name or display name
     */
    public function searchRoles(string $query): Collection
    {
        return Role::where(function ($q) use ($query) {
            $q->where('name', 'like', '%'.$query.'%')
              ->orWhere('display_name', 'like', '%'.$query.'%')
              ->orWhere('description', 'like', '%'.$query.'%');
        })
        ->with(['permissions', 'users'])
        ->limit(50)
        ->get();
    }

    /**
     * Check if role name is unique (excluding current role)
     */
    public function isRoleNameUnique(string $name, ?int $excludeId = null): bool
    {
        $query = Role::where('name', $name);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    /**
     * Get roles that can be assigned to users (excluding system roles)
     */
    public function getAssignableRoles(): Collection
    {
        return Role::whereNotIn('name', ['super-admin'])
            ->orderBy('display_name')
            ->get();
    }

    /**
     * Clone role with permissions
     */
    public function cloneRole(int $id, string $newName, string $newDisplayName): Role
    {
        return DB::transaction(function () use ($id, $newName, $newDisplayName) {
            $sourceRole = Role::findOrFail($id);
            
            $newRole = Role::create([
                'name' => $newName,
                'display_name' => $newDisplayName,
                'description' => $sourceRole->description,
                'guard_name' => 'web',
            ]);

            // Copy permissions
            $permissions = $sourceRole->permissions->pluck('name')->toArray();
            $newRole->syncPermissions($permissions);

            Log::info('Role cloned', [
                'source_role_id' => $sourceRole->id,
                'source_role_name' => $sourceRole->name,
                'new_role_id' => $newRole->id,
                'new_role_name' => $newRole->name,
                'cloned_by' => auth()->id(),
            ]);

            return $newRole;
        });
    }
}