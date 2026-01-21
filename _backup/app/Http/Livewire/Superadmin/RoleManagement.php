<?php

namespace App\Http\Livewire\Superadmin;

use App\Models\Permission;
use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;

class RoleManagement extends Component
{
    use WithPagination;

    public $search = '';

    public $showEditModal = false;

    public $showDeleteModal = false;

    public $roleToDelete = null;

    // Form fields
    public $name;

    public $display_name;

    public $description;

    public $selectedModules = [];

    public $availableModules = [];

    protected $rules = [
        'name' => 'required|string|max:255|unique:roles,name',
        'display_name' => 'required|string|max:255',
        'description' => 'nullable|string|max:500',
        'selectedModules' => 'required|array|min:1',
    ];

    public function mount()
    {
        $this->availableModules = $this->getAvailableModules();
    }

    public function render()
    {
        $roles = Role::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('display_name', 'like', '%'.$this->search.'%');
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.superadmin.role-management', [
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->showEditModal = true;
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $this->role = $role;
        $this->name = $role->name;
        $this->display_name = $role->display_name;
        $this->description = $role->description;

        // Get current permissions for this role
        $this->selectedModules = [];
        foreach ($role->permissions as $permission) {
            // Extract module name from permission name
            $moduleName = explode('.', $permission->name)[0];
            if (! in_array($moduleName, $this->selectedModules)) {
                $this->selectedModules[] = $moduleName;
            }
        }

        $this->showEditModal = true;
    }

    public function update()
    {
        $this->rules['name'] = 'required|string|max:255|unique:roles,name,'.($this->role->id ?? '');

        $validatedData = $this->validate();

        if (isset($this->role->id)) {
            // Update existing role
            $role = Role::findOrFail($this->role->id);
            $role->update([
                'name' => $validatedData['name'],
                'display_name' => $validatedData['display_name'],
                'description' => $validatedData['description'],
            ]);

            // Sync permissions based on selected modules
            $permissions = [];
            foreach ($validatedData['selectedModules'] as $module) {
                if (isset($this->availableModules[$module]['permissions'])) {
                    foreach ($this->availableModules[$module]['permissions'] as $permission) {
                        $permissions[] = $permission;
                    }
                }
            }

            $role->syncPermissions($permissions);
            session()->flash('message', 'Role updated successfully.');
        } else {
            // Create new role
            $role = Role::create([
                'name' => $validatedData['name'],
                'display_name' => $validatedData['display_name'],
                'description' => $validatedData['description'],
            ]);

            // Assign permissions based on selected modules
            $permissions = [];
            foreach ($validatedData['selectedModules'] as $module) {
                if (isset($this->availableModules[$module]['permissions'])) {
                    foreach ($this->availableModules[$module]['permissions'] as $permission) {
                        $permissions[] = $permission;
                    }
                }
            }

            $role->givePermissionTo($permissions);
            session()->flash('message', 'Role created successfully.');
        }

        $this->closeModal();
        $this->resetInputFields();
    }

    public function confirmDelete($id)
    {
        $this->roleToDelete = Role::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $role = Role::findOrFail($this->roleToDelete->id);
        $role->delete();
        $this->closeDeleteModal();
        session()->flash('message', 'Role deleted successfully.');
    }

    public function closeModal()
    {
        $this->showEditModal = false;
        $this->resetInputFields();
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->display_name = '';
        $this->description = '';
        $this->selectedModules = [];
        $this->role = null;
    }

    private function getAvailableModules()
    {
        return [
            'dashboard' => [
                'label' => 'Dashboard',
                'permissions' => [
                    'dashboard.view',
                ],
            ],
            'users' => [
                'label' => 'Manajemen Pengguna',
                'permissions' => [
                    'users.view',
                    'users.create',
                    'users.edit',
                    'users.delete',
                ],
            ],
            'roles' => [
                'label' => 'Manajemen Peran',
                'permissions' => [
                    'roles.view',
                    'roles.create',
                    'roles.edit',
                    'roles.delete',
                ],
            ],
            'employees' => [
                'label' => 'Manajemen Karyawan',
                'permissions' => [
                    'employees.view',
                    'employees.create',
                    'employees.edit',
                    'employees.delete',
                ],
            ],
            'dosens' => [
                'label' => 'Manajemen Dosen',
                'permissions' => [
                    'dosens.view',
                    'dosens.create',
                    'dosens.edit',
                    'dosens.delete',
                ],
            ],
            'slip-gaji' => [
                'label' => 'Slip Gaji',
                'permissions' => [
                    'slip-gaji.view',
                    'slip-gaji.create',
                    'slip-gaji.edit',
                    'slip-gaji.delete',
                    'slip-gaji.upload',
                    'slip-gaji.download',
                ],
            ],
            'fingerprint' => [
                'label' => 'Mesin Fingerprint',
                'permissions' => [
                    'fingerprint.view',
                    'fingerprint.create',
                    'fingerprint.edit',
                    'fingerprint.delete',
                    'fingerprint.sync',
                ],
            ],
            'activity-logs' => [
                'label' => 'Log Aktivitas',
                'permissions' => [
                    'activity-logs.view',
                ],
            ],
        ];
    }
}
