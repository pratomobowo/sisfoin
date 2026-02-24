<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Set active role for user
        $user = Auth::user();
        $firstRole = $user->roles->first();
        if ($firstRole) {
            setActiveRole($firstRole->name);
        }

        AuditLogger::log(
            logName: 'auth',
            event: 'login',
            action: 'auth.session.login',
            description: 'User logged in',
            properties: [
                'login_time' => now()->toIso8601String(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
            metadata: [
                'module' => 'auth',
                'risk_level' => 'low',
                'result' => 'success',
            ],
            causer: $user,
        );

        // Unify redirect to single dashboard for all roles
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Log logout activity before logging out
        if ($user) {
            AuditLogger::log(
                logName: 'auth',
                event: 'logout',
                action: 'auth.session.logout',
                description: 'User logged out',
                properties: [
                    'logout_time' => now()->toIso8601String(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
                metadata: [
                    'module' => 'auth',
                    'risk_level' => 'low',
                    'result' => 'success',
                ],
                causer: $user,
            );
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
