<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        \Log::info('CheckActiveRole middleware called', [
            'uri' => $request->getRequestUri(),
            'method' => $request->getMethod(),
            'role' => $role,
            'user_authenticated' => auth()->check(),
            'user' => auth()->check() ? auth()->user()->email : null,
        ]);

        // Check if user is authenticated
        if (! auth()->check()) {
            \Log::info('User not authenticated, redirecting to login');
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Super-admin bypasses all role checks
        if ($user->hasRole('super-admin')) {
            \Log::info('User is super-admin, bypassing role check');
            return $next($request);
        }

        // Check if user has the required role
        if (! $user->hasRole($role)) {
            \Log::info('User does not have required role', [
                'required_role' => $role,
                'user_roles' => $user->roles->pluck('name')->toArray(),
            ]);
            abort(403, 'Access denied. You do not have the required role.');
        }

        // Get active role with fallback
        $activeRole = getActiveRole();
        
        // If no active role is set, set the first available role that matches the required role
        if (!$activeRole && $user->hasRole($role)) {
            setActiveRole($role);
            $activeRole = $role;
            \Log::info('No active role set, setting to required role', [
                'set_role' => $role,
            ]);
        }

        // Check if the active role matches the required role
        if ($activeRole !== $role) {
            \Log::info('Active role does not match required role', [
                'active_role' => $activeRole,
                'required_role' => $role,
                'user_roles' => $user->roles->pluck('name')->toArray(),
            ]);
            
            // If user has the required role but active role doesn't match, 
            // allow them to switch automatically if they have permission
            if ($user->hasRole($role)) {
                setActiveRole($role);
                \Log::info('Automatically switched active role to match required role', [
                    'switched_to' => $role,
                ]);
            } else {
                abort(403, 'Access denied. Please switch to the correct role.');
            }
        }

        \Log::info('Role check passed');

        return $next($request);
    }
}
