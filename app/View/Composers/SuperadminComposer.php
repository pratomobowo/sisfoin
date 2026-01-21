<?php

namespace App\View\Composers;

use App\Services\SuperadminService;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class SuperadminComposer
{
    public function __construct(
        private SuperadminService $superadminService
    ) {}

    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $view->with([
            'currentUser' => Auth::user(),
            'navigationStats' => $this->getNavigationStats(),
            'systemAlerts' => $this->getSystemAlerts(),
            'quickActions' => $this->getQuickActions(),
        ]);
    }

    /**
     * Get navigation statistics for sidebar.
     */
    private function getNavigationStats(): array
    {
        return Cache::remember('superadmin.navigation.stats', 300, function () {
            return [
                'total_users' => \App\Models\User::count(),
                'active_users' => \App\Models\User::count(),
                'total_roles' => \Spatie\Permission\Models\Role::count(),
                'today_attendance' => \App\Models\AttendanceLog::whereDate('datetime', today())->count(),
            ];
        });
    }

    /**
     * Get system alerts for notifications.
     */
    private function getSystemAlerts(): array
    {
        $alerts = [];

        // Check for users without roles
        $usersWithoutRoles = \App\Models\User::doesntHave('roles')->count();
        if ($usersWithoutRoles > 0) {
            $alerts[] = [
                'type' => 'info',
                'message' => "{$usersWithoutRoles} pengguna belum memiliki peran",
                'action_url' => route('superadmin.users.index'),
                'action_text' => 'Kelola Pengguna'
            ];
        }

        // Check for recent failed login attempts
        $recentFailedLogins = Cache::get('failed_login_attempts', 0);
        if ($recentFailedLogins > 10) {
            $alerts[] = [
                'type' => 'danger',
                'message' => "Terdeteksi {$recentFailedLogins} percobaan login gagal dalam 1 jam terakhir",
                'action_url' => '#',
                'action_text' => 'Lihat Log'
            ];
        }

        return $alerts;
    }

    /**
     * Get quick actions for the current user.
     */
    private function getQuickActions(): array
    {
        $actions = [];

        // Add user management actions
        if (Auth::user()->can('create users')) {
            $actions[] = [
                'title' => 'Tambah Pengguna',
                'description' => 'Buat akun pengguna baru',
                'icon' => 'user-plus',
                'url' => route('superadmin.users.create'),
                'color' => 'primary'
            ];
        }

        // Add role management actions
        if (Auth::user()->can('manage roles')) {
            $actions[] = [
                'title' => 'Kelola Peran',
                'description' => 'Atur peran dan izin pengguna',
                'icon' => 'shield-check',
                'url' => route('superadmin.roles.index'),
                'color' => 'info'
            ];
        }

        // Add sync action (Updated to pull data)
        $actions[] = [
            'title' => 'Tarik Data Absensi',
            'description' => 'Tarik data absensi dari ADMS',
            'icon' => 'refresh',
            'url' => route('superadmin.fingerprint.attendance-logs.index'),
            'color' => 'warning',
        ];

        return $actions;
    }
}