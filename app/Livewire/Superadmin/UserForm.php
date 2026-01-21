<?php

namespace App\Livewire\Superadmin;

use App\Models\User;
use App\Models\Role;
use App\Models\FingerprintUserMapping;
use App\Services\UserService;
use Livewire\Component;
use Livewire\WithFileUploads;

class UserForm extends Component
{
    public $userId;
    public $name;
    public $email;
    public $nip;
    public $employee_type;
    public $employee_id;
    public $fingerprint_pin;
    public $fingerprint_enabled = false;
    public $password;
    public $password_confirmation;
    public $selectedRoles = [];
    
    // Fingerprint autocomplete properties removed
    
    public $roles = [];
    public $isEditing = false;
    public $title = 'Tambah Pengguna';

    protected $listeners = [
        // 'fingerprintUserSelected' => 'updateFingerprintPin', // Removed
    ];

    private $userService;

    public function boot(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function mount($userId = null)
    {
        $this->userId = $userId;
        $this->roles = $this->userService->getAllRoles();

        if ($this->userId) {
            $this->isEditing = true;
            $this->title = 'Edit Pengguna';
            $this->loadUserData();
        }
    }

    private function loadUserData()
    {
        $user = $this->userService->getUserById($this->userId);
        
        if ($user) {
            $this->name = $user->name;
            $this->email = $user->email;
            $this->nip = $user->nip;
            $this->employee_type = $user->employee_type;
            $this->employee_id = $user->employee_id;
            $this->fingerprint_pin = $user->fingerprint_pin;
            $this->fingerprint_enabled = (bool) $user->fingerprint_enabled;
            $this->selectedRoles = $user->roles->pluck('name')->toArray();
        }
    }

    public function updatedFingerprintEnabled($value)
    {
        // Log when fingerprint_enabled is changed
        \Log::info('Fingerprint enabled changed', [
            'value' => $value,
            'user_id' => $this->userId,
            'fingerprint_pin' => $this->fingerprint_pin,
        ]);
        
        // If fingerprint is being enabled but no PIN is set, show a warning
        if ($value && empty($this->fingerprint_pin)) {
            // Warning handled by parent component or validation
        }
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($this->userId ?: 'NULL'),
            'nip' => 'nullable|string|max:50|unique:users,nip,' . ($this->userId ?: 'NULL'),
            'employee_type' => 'nullable|string|in:employee,dosen',
            'employee_id' => 'nullable|string|max:50',
            'fingerprint_pin' => 'nullable|string|max:50',
            'selectedRoles' => 'required|array|min:1',
        ];

        if (!$this->isEditing) {
            $rules['password'] = 'required|min:8|confirmed';
        } else {
            $rules['password'] = 'nullable|min:8|confirmed';
        }

        $validatedData = $this->validate($rules);

        // Prepare data for service
        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'nip' => $this->nip,
            'employee_type' => $this->employee_type,
            'employee_id' => $this->employee_id,
            'fingerprint_pin' => $this->fingerprint_pin,
            'fingerprint_enabled' => $this->fingerprint_enabled,
            'roles' => $this->selectedRoles,
        ];

        if (!empty($this->password)) {
            $userData['password'] = $this->password;
        }

        try {
            \Log::info('Attempting to save user data', [
                'user_id' => $this->userId,
                'is_editing' => $this->isEditing,
                'data' => $userData
            ]);

            if ($this->isEditing) {
                $user = $this->userService->updateUser($this->userId, $userData);
                \Log::info('User updated successfully', ['user' => $user->id]);
                session()->flash('success', 'Pengguna berhasil diperbarui.');
            } else {
                $user = $this->userService->createUser($userData);
                \Log::info('User created successfully', ['user' => $user->id]);
                session()->flash('success', 'Pengguna berhasil dibuat.');
            }

            return redirect()->route('superadmin.users.index');
        } catch (\Exception $e) {
            \Log::error('Error saving user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $userData
            ]);
            session()->flash('error', 'Gagal menyimpan pengguna: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.superadmin.user-form');
    }
}
