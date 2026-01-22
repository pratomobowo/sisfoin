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
        $requiredRoles = explode('|', $role);

        // Get active role with fallback
        $activeRole = getActiveRole();

        // 1. Automatic Role Switching
        // If the current active role doesn't match the required role(s), 
        // but the user HAS one of the required roles, switch to it automatically.
        if (!in_array($activeRole, $requiredRoles)) {
            $matchedRole = null;
            foreach ($requiredRoles as $r) {
                if ($user->hasRole($r)) {
                    $matchedRole = $r;
                    break;
                }
            }

            if ($matchedRole) {
                setActiveRole($matchedRole);
                $activeRole = $matchedRole;
                \Log::info('Automatically switched active role to match required role', [
                    'switched_to' => $matchedRole,
                ]);
            }
        }

        // 2. Permission Check
        // User passes if:
        // - Their active role is one of the required roles
        // - They are a super-admin (bypass)
        if (in_array($activeRole, $requiredRoles) || $user->hasRole('super-admin')) {
            \Log::info('Role check passed', [
                'active_role' => $activeRole,
                'is_super_admin' => $user->hasRole('super-admin'),
            ]);
            return $next($request);
        }

        // 3. Access Denied
        \Log::info('Role check failed', [
            'active_role' => $activeRole,
            'required_roles' => $requiredRoles,
            'user_roles' => $user->roles->pluck('name')->toArray(),
        ]);
        
        abort(403, 'Access denied. Please switch to the correct role.');

        return $next($request);
    }
}
