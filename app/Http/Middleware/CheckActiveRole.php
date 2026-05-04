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

        $activeRole = getActiveRole();

        // Authorization is based on assigned roles only. Active role is UI context and
        // must not be mutated by middleware as a side effect of visiting a route.
        if ($user->hasAnyRole($requiredRoles) || $user->hasRole('super-admin')) {
            \Log::info('Role check passed', [
                'active_role' => $activeRole,
                'required_roles' => $requiredRoles,
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
        
        abort(403, 'Access denied.');

        return $next($request);
    }
}
