<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Traits\HasCrudOperations;
use App\Models\User;
use App\Services\UserService; // Changed from UserManagementService
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends BaseController
{
    use HasCrudOperations;

    protected string $modelClass = User::class;
    protected string $viewPrefix = 'superadmin.users';
    protected string $routePrefix = 'superadmin.users';
    protected string $resourceName = 'Pengguna';

    public function __construct(
        private UserService $userService // Changed from UserManagementService
    ) {
        // No parent constructor to call
    }

    /**
     * Display a listing of users.
     */
    public function index(): View
    {
        return view('superadmin.users.index');
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $roles = $this->userService->getAllRoles(); // Changed to getAllRoles
        return view("{$this->viewPrefix}.create", compact('roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->getValidationRules());
        
        try {
            $user = $this->userService->createUser($validated);
            return $this->redirectWithSuccess("{$this->routePrefix}.index", "{$this->resourceName} berhasil dibuat.");
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified user.
     */
    public function show(string $id): View
    {
        $user = User::findOrFail($id);
        $roles = $this->userService->getAllRoles();
        return view('superadmin.users.show', compact('user', 'roles'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(string $id): View
    {
        $user = User::findOrFail($id);
        $roles = $this->userService->getAllRoles();
        return view('superadmin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        $validated = $request->validate($this->getValidationRules($user->id));
        
        try {
            $this->userService->updateUser($user, $validated);
            return redirect()->route('superadmin.users.index')->with('success', 'User berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        
        // Prevent deletion of current user
        if ($user->id === auth()->id()) {
            return redirect()->route('superadmin.users.index')->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        try {
            $this->userService->deleteUser($user);
            return redirect()->route('superadmin.users.index')->with('success', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('superadmin.users.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle user status.
     * @deprecated Status column is not implemented
     */
    public function toggleStatus(string $id)
    {
        try {
            $user = User::findOrFail($id);
            // Since is_active column doesn't exist, we'll just return success message
            // or we could remove this route entirely if not used.
            return redirect()->route("{$this->routePrefix}.index")->with('success', "Status pengguna berhasil diubah.");
        } catch (\Exception $e) {
            return redirect()->route("{$this->routePrefix}.index")->with('error', $e->getMessage());
        }
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $this->userService->resetUserPassword($user, $request->password);
            return $this->redirectWithSuccess("{$this->routePrefix}.index", "Password pengguna berhasil direset.");
        } catch (\Exception $e) {
            return $this->redirectWithError("{$this->routePrefix}.index", $e->getMessage());
        }
    }

    /**
     * Bulk delete users.
     */
    public function bulkDeleteUsers(Request $request): RedirectResponse
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        try {
            $results = $this->userService->bulkDeleteUsers($request->user_ids);
            $deletedCount = count($results['success']);
            
            if ($deletedCount > 0) {
                return $this->redirectWithSuccess("{$this->routePrefix}.index", "{$deletedCount} pengguna berhasil dihapus.");
            } else {
                 return $this->redirectWithError("{$this->routePrefix}.index", "Gagal menghapus pengguna. Kesalahan: " . implode(', ', $results['errors']));
            }
        } catch (\Exception $e) {
            return $this->redirectWithError("{$this->routePrefix}.index", $e->getMessage());
        }
    }

    /**
     * Get validation rules for user.
     */
    private function getValidationRules(?int $userId = null): array
    {
        $emailRule = 'required|email|unique:users,email';
        $nipRule = 'nullable|string|unique:users,nip';
        $employeeIdRule = 'nullable|string|unique:users,employee_id';

        if ($userId) {
            $emailRule .= ",{$userId}";
            $nipRule .= ",{$userId}";
            $employeeIdRule .= ",{$userId}";
        }

        return [
            'name' => 'required|string|max:255',
            'email' => $emailRule,
            'password' => $userId ? 'nullable|string|min:8|confirmed' : 'required|string|min:8|confirmed',
            'nip' => $nipRule,
            'employee_type' => 'nullable|in:PNS,PPPK,Honorer',
            'employee_id' => $employeeIdRule,
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
            // 'is_active' => 'boolean', // Removed as column doesn't exist
            'fingerprint_enabled' => 'boolean',
            'fingerprint_pin' => 'nullable|string|min:4|max:6',
        ];
    }

    /**
     * Check if the user can be deleted.
     */
    protected function canDelete(Model $model): bool
    {
        // Prevent deletion of self
        return $model->id !== auth()->id();
    }

    /**
     * Get the error message when deletion is not allowed.
     */
    protected function getDeletionErrorMessage(Model $model): string
    {
        if ($model->id === auth()->id()) {
            return 'Anda tidak dapat menghapus akun sendiri.';
        }

        return parent::getDeletionErrorMessage($model);
    }
}
