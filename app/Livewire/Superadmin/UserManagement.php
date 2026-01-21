<?php

namespace App\Livewire\Superadmin;

use App\Models\Role;
use App\Services\UserService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class UserManagement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $selectedRole = '';
    public $selectedFingerprintStatus = '';
    public $perPage = 10;
    public $page = 1;

    // Delete confirmation
    public $userToDelete;
    public $showDeleteModal = false;
    

    protected $listeners = [
        'userSaved' => 'handleUserSaved',
        'userDeleted' => 'handleUserDeleted',
        'usersImported' => 'handleUsersImported',
    ];

    private $userService;

    public function boot(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function mount()
    {
        // Check if edit parameter exists in URL
        if (request()->has('edit')) {
            $userId = request()->input('edit');
            if ($userId && is_numeric($userId)) {
                // Dispatch event to UserForm component
                $this->dispatch('openUserForm', $userId);
            }
        }
    }

    public function render()
    {
        $roles = $this->userService->getAllRoles();

        $filters = [
            'search' => $this->search,
            'role' => $this->selectedRole,
        ];

        // Map status to filter
        if ($this->selectedFingerprintStatus === 'active') {
             $filters['fingerprint_enabled'] = true;
        } elseif ($this->selectedFingerprintStatus === 'disabled') {
             $filters['fingerprint_enabled'] = false;
        } elseif ($this->selectedFingerprintStatus === 'no_pin') {
            // This case requires special handling in UserService if needed, 
            // for now we might treat it as enabled but no pin? 
            // Or if UserService doesn't support it, we skip.
            // Let's assume for now we just filter by enabled=true
             $filters['fingerprint_enabled'] = true;
        }

        $users = $this->userService->getUsers($filters, $this->perPage);

        return view('livewire.superadmin.user-management', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }


    public function confirmDelete($id)
    {
        try {
            $this->userToDelete = $this->userService->getUserById($id);
            $this->showDeleteModal = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Pengguna tidak ditemukan');
        }
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->userToDelete = null;
    }

    public function delete()
    {
        try {
            if ($this->userToDelete) {
                $this->userService->deleteUser($this->userToDelete->id);
                $this->showDeleteModal = false;
                $this->userToDelete = null;

                session()->flash('success', 'Pengguna berhasil dihapus');
                $this->dispatch('userDeleted');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus pengguna: ' . $e->getMessage());
        } finally {
            // Ensure modal is closed even if there's an error
            $this->showDeleteModal = false;
            $this->userToDelete = null;
        }
    }

    public function showImportUsers()
    {
        $this->dispatch('showUserImportModal');
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->selectedRole = '';
        $this->selectedFingerprintStatus = '';
        $this->perPage = 10;
        $this->resetPage();
    }

    public function nextPage()
    {
        $this->setPage($this->page + 1);
    }

    public function previousPage()
    {
        $this->setPage(max(1, $this->page - 1));
    }

    public function gotoPage($page)
    {
        $this->setPage($page);
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedRole()
    {
        $this->resetPage();
    }

    public function updatedSelectedFingerprintStatus()
    {
        $this->resetPage();
    }

    public function handleUserSaved($params = [])
    {
        $message = $params['message'] ?? 'Pengguna berhasil disimpan';
        session()->flash('success', $message);
        $this->resetPage();
    }

    public function handleUserDeleted()
    {
        session()->flash('success', 'Pengguna berhasil dihapus');
        $this->resetPage();
    }

    public function handleUsersImported()
    {
        // Import message is already set in the importUsers method
        $this->resetPage();
    }
}
