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
        $this->selectedModules = $this->getRoleModules($role);
    }

    private function loadModulesConfig()
    {
        $configPath = storage_path('app/modules_config.json');
        if (file_exists($configPath)) {
            $this->availableModules = json_decode(file_get_contents($configPath), true);
        } else {
            // Fallback if config file doesn't exist
            $this->availableModules = [
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
        $permissions = [];

        // If selectedPermissions is set (for testing), use it directly
        if (! empty($this->selectedPermissions)) {
            $permissions = $this->selectedPermissions;
        } else {
            // Collect permissions from selected modules
            foreach ($this->selectedModules as $moduleKey) {
                if (isset($this->availableModules[$moduleKey]['permissions'])) {
                    $modulePermissions = array_keys($this->availableModules[$moduleKey]['permissions']);
                    $permissions = array_merge($permissions, $modulePermissions);
                }
            }
        }

        // Special handling for staff role - only view permissions + profile.edit
        if ($role->name === 'staff') {
            $staffPermissions = array_filter($permissions, function ($permission) {
                return strpos($permission, '.view') !== false || $permission === 'profile.edit';
            });

            $permissions = array_unique($staffPermissions);
        }

        // Sync permissions
        $role->syncPermissions($permissions);

        // Log the actual permissions being synced for debugging
        Log::info('Syncing permissions for role: '.$role->name, [
            'selected_modules' => $this->selectedModules,
            'selected_permissions' => $this->selectedPermissions,
            'permissions_to_sync' => $permissions,
        ]);
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
