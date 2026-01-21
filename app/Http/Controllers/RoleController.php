<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
        if (! auth()->user()->hasRole($role)) {
            return back()->with('error', 'You do not have permission to switch to this role.');
        }

        // Set the active role
        setActiveRole($role);

        return back()->with('success', "Successfully switched to {$role} role.");
    }

    /**
     * Get available roles for the current user
     */
    public function getAvailableRoles()
    {
        return response()->json([
            'roles' => auth()->user()->roles->pluck('name')->toArray(),
            'active_role' => getActiveRole(),
        ]);
    }
}
