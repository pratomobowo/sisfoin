<?php

namespace App\Livewire\Superadmin;

use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class RoleForm extends Component
{
    public $roleId = null;

    public $name = '';

    public $display_name = '';

    public $description = '';

    public $selectedModules = [];

    public $availableModules = [];

    public $selectedPermissions = [];

    protected $queryString = ['roleId'];

    protected $rules = [
        'name' => 'required|string|max:255|unique:roles,name|regex:/^[a-z0-9\-_]+$/',
        'display_name' => 'required|string|max:255',
        'description' => 'nullable|string|max:500',
        'selectedModules' => 'array',
        'selectedPermissions' => 'array',
    ];

    protected $listeners = [
        'editRole' => 'edit',
        'resetForm' => 'resetForm',
    ];

    public function mount($roleId = null)
    {
        $this->roleId = $roleId;
        $this->loadModulesConfig();
        
        // If roleId is provided, load the role data
        if ($this->roleId) {
            $this->loadRoleData();
        }
    }

    private function loadRoleData()
    {
        if (!$this->roleId) {
            return;
        }

        $role = Role::findOrFail($this->roleId);

        // Prevent editing super-admin role
        if ($role->name === 'super-admin') {
            return;
        }

        $this->name = $role->name;
        $this->display_name = $role->display_name ?? $role->name;
        $this->description = $role->description ?? '';

        // Load current modules for this role
        $this->selectedPermissions = $role->permissions->pluck('name')->values()->toArray();
        $this->selectedModules = $this->getRoleModules($role);
    }

    private function loadModulesConfig()
    {
        $this->availableModules = config('modules', []);
    }

    public function edit($roleId)
    {
        // If roleId is null, it means we're creating a new role
        if ($roleId === null) {
            $this->resetForm();
            $this->dispatch('showModal');
            return;
        }

        $role = Role::findOrFail($roleId);

        // Prevent editing super-admin role
        if ($role->name === 'super-admin') {
            $this->dispatch('closeModal');
            return;
        }

        $this->roleId = $roleId;
        $this->name = $role->name;
        $this->display_name = $role->display_name ?? $role->name;
        $this->description = $role->description ?? '';

        // Load current modules for this role
        $this->selectedPermissions = $role->permissions->pluck('name')->values()->toArray();
        $this->selectedModules = $this->getRoleModules($role);

        $this->dispatch('showModal');
    }

    private function getRoleModules($role)
    {
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $selectedModules = [];

        foreach ($this->availableModules as $moduleKey => $moduleData) {
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

    public function save()
    {
        // Adjust validation rules for edit mode
        if ($this->roleId) {
            $this->rules['name'] = 'required|string|max:255|unique:roles,name,'.$this->roleId;
        }

        $this->validate();

        try {
            if ($this->roleId) {
                // Update existing role
                $role = Role::findOrFail($this->roleId);

                // Prevent editing super-admin role
                if ($role->name === 'super-admin') {
                    return redirect()->route('superadmin.roles.index');
                }

                $role->update([
                    'name' => $this->name,
                    'display_name' => $this->display_name,
                    'description' => $this->description,
                ]);
            } else {
                // Create new role
                $role = Role::create([
                    'name' => $this->name,
                    'display_name' => $this->display_name,
                    'description' => $this->description,
                    'guard_name' => 'web',
                ]);
            }

            // Sync permissions based on selected modules
            $this->syncRolePermissions($role);

            // Log activity
            Log::info('Role '.($this->roleId ? 'updated' : 'created').': '.$role->name, [
                'user_id' => auth()->id(),
                'role_id' => $role->id,
                'selected_modules' => $this->selectedModules,
            ]);

            // Flash success message before redirect
            session()->flash('success', 'Role berhasil '.($this->roleId ? 'diperbarui' : 'dibuat'));

            // Redirect to index page
            return redirect()->route('superadmin.roles.index');

        } catch (\Exception $e) {
            Log::error('Error saving role: '.$e->getMessage());
        }
    }

    private function syncRolePermissions($role)
    {
        $permissions = $this->selectedPermissions;

        // Special handling for staff role - only view permissions + profile.edit
        if ($role->name === 'staff') {
            $staffPermissions = array_filter($permissions, function ($permission) {
                return strpos($permission, '.view') !== false || $permission === 'profile.edit';
            });

            $permissions = array_unique($staffPermissions);
        }

        // Sync permissions
        $role->syncPermissions($permissions);
        $this->selectedPermissions = array_values(array_unique($permissions));
        $this->selectedModules = $this->getModulesFromPermissions($this->selectedPermissions);

        // Log the actual permissions being synced for debugging
        Log::info('Syncing permissions for role: '.$role->name, [
            'selected_modules' => $this->selectedModules,
            'selected_permissions' => $this->selectedPermissions,
            'permissions_to_sync' => $permissions,
        ]);
    }

    public function getModulePermissionNames(string $moduleKey): array
    {
        return array_keys($this->availableModules[$moduleKey]['permissions'] ?? []);
    }

    public function getModuleSelectionCount(string $moduleKey): array
    {
        $modulePermissions = $this->getModulePermissionNames($moduleKey);

        return [
            'selected' => count(array_intersect($modulePermissions, $this->selectedPermissions)),
            'total' => count($modulePermissions),
        ];
    }

    public function isModuleFullySelected(string $moduleKey): bool
    {
        $counts = $this->getModuleSelectionCount($moduleKey);

        return $counts['total'] > 0 && $counts['selected'] === $counts['total'];
    }

    public function toggleModulePermissions(string $moduleKey): void
    {
        $modulePermissions = $this->getModulePermissionNames($moduleKey);

        if ($this->isModuleFullySelected($moduleKey)) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $modulePermissions));
        } else {
            $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $modulePermissions)));
        }

        $this->selectedModules = $this->getModulesFromPermissions($this->selectedPermissions);
    }

    private function getModulesFromPermissions(array $permissions): array
    {
        $selectedModules = [];

        foreach ($this->availableModules as $moduleKey => $moduleData) {
            $modulePermissions = array_keys($moduleData['permissions'] ?? []);

            if (array_intersect($modulePermissions, $permissions)) {
                $selectedModules[] = $moduleKey;
            }
        }

        return $selectedModules;
    }

    public function resetForm()
    {
        $this->roleId = null;
        $this->name = '';
        $this->display_name = '';
        $this->description = '';
        $this->selectedModules = [];
        $this->selectedPermissions = [];
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.superadmin.role-form', [
            'availableModules' => $this->availableModules,
        ]);
    }
}
