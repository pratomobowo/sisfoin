<?php

namespace App\Livewire\Superadmin;

use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
class RoleManagement extends Component
{
    use WithPagination;

    public $search = '';

    public $perPage = 10;

    // Advanced Filters
    public $filterUserCount = '';

    public $filterModule = '';

    public $showFilters = false;

    // Bulk Operations
    public $selectedRoles = [];

    public $selectAll = false;

    public $showBulkActions = false;

    public $bulkAction = '';

    public $showBulkPermissionModal = false;

    public $bulkPermissionModule = '';

    public $showCreateModal = false;

    public $showDeleteModal = false;

    public $roleToDelete;

    protected $listeners = [
        'roleSaved' => 'handleRoleSaved',
        'roleDeleted' => 'handleRoleDeleted',
        'showModal' => 'showModal',
        'closeModal' => 'closeModal',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterUserCount()
    {
        $this->resetPage();
    }

    public function updatedFilterModule()
    {
        $this->resetPage();
    }

    // Pagination methods
    public function gotoPage($page)
    {
        $this->setPage($page);
    }

    public function nextPage()
    {
        $this->setPage($this->page + 1);
    }

    public function previousPage()
    {
        $this->setPage($this->page - 1);
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filterUserCount = '';
        $this->filterModule = '';
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    // Bulk Operations Methods
    public function updatedSelectAll($value)
    {
        if ($value) {
            $query = Role::query();
            
            // Apply same filters as render method
            if ($this->search) {
                $query->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('display_name', 'like', '%'.$this->search.'%');
            }

            if ($this->filterUserCount) {
                switch ($this->filterUserCount) {
                    case 'has_users':
                        $query->whereHas('users');
                        break;
                    case 'no_users':
                        $query->whereDoesntHave('users');
                        break;
                }
            }

            if ($this->filterModule) {
                $query->whereHas('permissions', function ($q) {
                    $q->where('name', 'like', $this->filterModule . '.%');
                });
            }

            $this->selectedRoles = $query->pluck('id')->toArray();
        } else {
            $this->selectedRoles = [];
        }
    }

    public function updatedSelectedRoles()
    {
        $this->selectAll = count($this->selectedRoles) === $this->getFilteredRolesCount();
        $this->showBulkActions = count($this->selectedRoles) > 0;
    }

    private function getFilteredRolesCount()
    {
        $query = Role::query();
        
        // Apply same filters as render method
        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('display_name', 'like', '%'.$this->search.'%');
        }

        if ($this->filterUserCount) {
            switch ($this->filterUserCount) {
                case 'has_users':
                    $query->whereHas('users');
                    break;
                case 'no_users':
                    $query->whereDoesntHave('users');
                    break;
            }
        }

        if ($this->filterModule) {
            $query->whereHas('permissions', function ($q) {
                $q->where('name', 'like', $this->filterModule . '.%');
            });
        }

        return $query->count();
    }

    public function clearSelection()
    {
        $this->selectedRoles = [];
        $this->selectAll = false;
        $this->showBulkActions = false;
    }

    public function executeBulkAction()
    {
        try {
            switch ($this->bulkAction) {
                case 'delete':
                    $this->bulkDelete();
                    break;
                case 'assign_permissions':
                    $this->showBulkPermissionModal = true;
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Error executing bulk action: '.$e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat mengeksekusi aksi bulk.');
        }
    }

    public function bulkDelete()
    {
        $roles = Role::whereIn('id', $this->selectedRoles)
            ->where('name', '!=', 'super-admin')
            ->get();

        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($roles as $role) {
            if ($role->users()->count() === 0) {
                $role->delete();
                $deletedCount++;
                Log::info('Bulk role deleted: '.$role->name, [
                    'user_id' => auth()->id(),
                    'role_id' => $role->id,
                ]);
            } else {
                $skippedCount++;
            }
        }

        $message = '';
        if ($deletedCount > 0) {
            $message .= $deletedCount.' role berhasil dihapus. ';
        }
        if ($skippedCount > 0) {
            $message .= $skippedCount.' role tidak dapat dihapus karena masih memiliki pengguna.';
        }

        session()->flash('success', $message);
        $this->clearSelection();
    }

    public function bulkAssignPermissions()
    {
        if (empty($this->bulkPermissionModule)) {
            session()->flash('error', 'Silakan pilih modul terlebih dahulu.');
            return;
        }

        $roles = Role::whereIn('id', $this->selectedRoles)->get();
        $configPath = storage_path('app/modules_config.json');
        
        if (file_exists($configPath)) {
            $availableModules = json_decode(file_get_contents($configPath), true);
        } else {
            $availableModules = [
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

        if (isset($availableModules[$this->bulkPermissionModule]['permissions'])) {
            $permissions = array_keys($availableModules[$this->bulkPermissionModule]['permissions']);
            
            foreach ($roles as $role) {
                $role->givePermissionTo($permissions);
                
                Log::info('Bulk permissions assigned to role: '.$role->name, [
                    'user_id' => auth()->id(),
                    'role_id' => $role->id,
                    'module' => $this->bulkPermissionModule,
                    'permissions' => $permissions,
                ]);
            }

            session()->flash('success', count($roles).' role berhasil diberikan hak akses modul '.$availableModules[$this->bulkPermissionModule]['label'].'.');
        }

        $this->showBulkPermissionModal = false;
        $this->bulkPermissionModule = '';
        $this->clearSelection();
    }

    public function create()
    {
        $this->showCreateModal = true;
    }


    public function edit($roleId)
    {
        // Redirect to edit page
        return redirect()->route('superadmin.roles.edit', $roleId);
    }

    public function confirmDelete($roleId)
    {
        $role = Role::findOrFail($roleId);

        // Prevent deleting super-admin role
        if ($role->name === 'super-admin') {
            session()->flash('error', 'Role Super Admin tidak dapat dihapus.');

            return;
        }

        // Check if role has users - if so, don't show delete modal
        if ($role->users()->count() > 0) {
            session()->flash('error', 'Role tidak dapat dihapus karena masih memiliki pengguna.');

            return;
        }

        $this->roleToDelete = $role;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        try {
            if ($this->roleToDelete && $this->roleToDelete->name !== 'super-admin') {
                // Check if role has users
                if ($this->roleToDelete->users()->count() > 0) {
                    session()->flash('error', 'Role tidak dapat dihapus karena masih memiliki pengguna.');
                    $this->showDeleteModal = false;

                    return;
                }

                Log::info('Role deleted: '.$this->roleToDelete->name, [
                    'user_id' => auth()->id(),
                    'role_id' => $this->roleToDelete->id,
                ]);

                $this->roleToDelete->delete();
                session()->flash('success', 'Role berhasil dihapus!');
            }
        } catch (\Exception $e) {
            Log::error('Error deleting role: '.$e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat menghapus role.');
        }

        $this->showDeleteModal = false;
        $this->roleToDelete = null;
    }

    public function showModal()
    {
        $this->showCreateModal = true;
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
    }

    /**
     * Handle role saved event
     */
    public function handleRoleSaved()
    {
        $this->showCreateModal = false;
    }

    /**
     * Handle role deleted event
     */
    public function handleRoleDeleted()
    {
        $this->showDeleteModal = false;
        $this->roleToDelete = null;
    }

    public function render()
    {
        // Build query with advanced filters
        $query = Role::with(['permissions', 'users']);

        // Search filter
        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('display_name', 'like', '%'.$this->search.'%');
        }

        // User count filter
        if ($this->filterUserCount) {
            switch ($this->filterUserCount) {
                case 'has_users':
                    $query->whereHas('users');
                    break;
                case 'no_users':
                    $query->whereDoesntHave('users');
                    break;
            }
        }

        // Module filter
        if ($this->filterModule) {
            $query->whereHas('permissions', function ($q) {
                $q->where('name', 'like', $this->filterModule . '.%');
            });
        }

        $roles = $query->paginate($this->perPage);

        // Load available modules for filter dropdown
        $configPath = storage_path('app/modules_config.json');
        if (file_exists($configPath)) {
            $availableModules = json_decode(file_get_contents($configPath), true);
        } else {
            $availableModules = [
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

        // Group permissions by category for the test
        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode('.', $permission->name);

            return $parts[0] ?? 'other';
        });

        return view('livewire.superadmin.role-management', [
            'roles' => $roles,
            'availableModules' => $availableModules,
            'permissions' => $permissions,
        ]);
    }
}
