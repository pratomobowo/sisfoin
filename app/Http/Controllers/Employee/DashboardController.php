<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee\Announcement;
use App\Models\Employee\Attendance;
use App\Models\SlipGajiDetail;
use Illuminate\View\View;
use Throwable;

class DashboardController extends Controller
{
    /**
     * Display the Employee module dashboard.
     */
    public function index(): View
    {
        $user = auth()->user();

        $attendanceDaysThisMonth = 0;
        $latestNetSalary = null;
        $unreadAnnouncements = 0;

        if ($user) {
            $attendanceDaysThisMonth = Attendance::query()
                ->where('user_id', $user->id)
                ->whereBetween('date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->whereIn('status', ['present', 'on_time', 'early_arrival', 'late'])
                ->count();

            if (! empty($user->nip)) {
                $latestNetSalary = SlipGajiDetail::query()
                    ->where('nip', $user->nip)
                    ->where('status', 'Tersedia')
                    ->orderByDesc('created_at')
                    ->value('penerimaan_bersih');
            }

            try {
                $unreadAnnouncements = Announcement::query()
                    ->active()
                    ->unreadBy($user)
                    ->count();
            } catch (Throwable) {
                $unreadAnnouncements = 0;
            }
        }

        return view('staff.dashboard', [
            'title' => 'Dashboard Staff',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Staff', 'url' => null],
            ],
            'quickStats' => [
                'attendance_days_this_month' => $attendanceDaysThisMonth,
                'latest_net_salary' => $latestNetSalary,
                'unread_announcements' => $unreadAnnouncements,
            ],
        ]);
    }
}
