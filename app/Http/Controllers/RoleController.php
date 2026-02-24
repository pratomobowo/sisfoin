<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    /**
     * Switch to a different role
     */
    public function switchRole(Request $request): RedirectResponse
    {
        $request->validate([
            'role' => 'required|string',
        ]);

        $role = $request->input('role');

        // Check if user has this role
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user || ! $user->hasRole($role)) {
            return back()->with('error', 'You do not have permission to switch to this role.');
        }

        // Set the active role
        setActiveRole($role);

        $targetRoute = in_array($role, ['staff', 'employee'], true)
            ? 'staff.dashboard'
            : 'dashboard';

        return redirect()->route($targetRoute)->with('success', "Successfully switched to {$role} role.");
    }

    /**
     * Get available roles for the current user
     */
    public function getAvailableRoles()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return response()->json([
            'roles' => $user ? $user->roles->pluck('name')->toArray() : [],
            'active_role' => getActiveRole(),
        ]);
    }
}
