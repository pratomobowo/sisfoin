<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee\Announcement;
use App\Models\Employee\Attendance;
use App\Models\SlipGajiDetail;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the staff dashboard.
     */
    public function index(): View
    {
        $user = auth()->user();

        // Calculate quick stats
        $attendanceDaysThisMonth = Attendance::query()
            ->where('user_id', $user->id)
            ->whereBetween('date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->whereIn('status', ['present', 'on_time', 'early_arrival', 'late'])
            ->count();

        // Get today's attendance
        $todayAttendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();

        // Get employee data
        $employeeData = null;
        if ($user->employee_id && $user->employee_type === 'employee') {
            $employeeData = \App\Models\Employee::find($user->employee_id);
        }

        $employeeInfo = [
            'status_kepegawaian' => $employeeData?->status_kepegawaian ?? 'Staff',
            'unit_kerja' => $employeeData?->satuan_kerja ?? '-',
            'nip' => $user->nip ?? '-',
        ];

        $latestNetSalary = null;
        $latestSalaryPeriod = null;
        $salaryErrorMessage = null;

        // Try to find salary by NIP first, then by name variations
        // Note: Status can be 'Tersedia' or 'KARYAWAN_TETAP' or other values
        if (! empty($user->nip)) {
            $latestSlip = SlipGajiDetail::query()
                ->with('header')
                ->where('nip', $user->nip)
                ->orderByDesc('created_at')
                ->first();

            if ($latestSlip) {
                $latestNetSalary = $latestSlip->penerimaan_bersih;
                $latestSalaryPeriod = $latestSlip->header?->periode;
            }
        }

        // If no salary found by NIP, try to find by name (for duplicate user accounts)
        if (is_null($latestNetSalary) && $user->name) {
            $nameParts = explode(' ', $user->name);
            $firstName = $nameParts[0] ?? '';
            $lastName = end($nameParts) ?? '';

            // Try to find any user with similar name that has NIP
            $similarUser = \App\Models\User::query()
                ->where('id', '!=', $user->id)
                ->whereNotNull('nip')
                ->where(function ($query) use ($firstName, $lastName) {
                    $query->where('name', 'like', "%{$firstName}%")
                        ->orWhere('name', 'like', "%{$lastName}%");
                })
                ->first();

            if ($similarUser) {
                $latestSlip = SlipGajiDetail::query()
                    ->with('header')
                    ->where('nip', $similarUser->nip)
                    ->orderByDesc('created_at')
                    ->first();

                if ($latestSlip) {
                    $latestNetSalary = $latestSlip->penerimaan_bersih;
                    $latestSalaryPeriod = $latestSlip->header?->periode;
                }
            }
        }

        if (is_null($latestNetSalary)) {
            if (empty($user->nip)) {
                $salaryErrorMessage = 'NIP belum diatur';
            } else {
                $salaryErrorMessage = 'Belum ada slip gaji';
            }
        }

        try {
            $unreadAnnouncements = Announcement::query()
                ->active()
                ->unreadBy($user)
                ->count();
        } catch (\Throwable) {
            $unreadAnnouncements = 0;
        }

        // Get recent announcements
        try {
            $announcements = Announcement::query()
                ->active()
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($announcement) use ($user) {
                    return [
                        'id' => $announcement->id,
                        'title' => $announcement->title,
                        'type' => $announcement->type,
                        'type_color' => $this->getTypeColor($announcement->type),
                        'is_pinned' => $announcement->is_pinned,
                        'is_read' => $announcement->readBy->contains($user),
                        'created_at' => $announcement->created_at,
                    ];
                });
        } catch (\Throwable) {
            $announcements = collect();
        }

        return view('staff.dashboard', [
            'title' => 'Dashboard Staff',
            'quickStats' => [
                'attendance_days_this_month' => $attendanceDaysThisMonth,
                'latest_net_salary' => $latestNetSalary,
                'latest_salary_period' => $latestSalaryPeriod,
                'unread_announcements' => $unreadAnnouncements,
                'salary_error_message' => $salaryErrorMessage,
            ],
            'announcements' => $announcements,
            'todayAttendance' => $todayAttendance,
            'employeeInfo' => $employeeInfo,
        ]);
    }

    /**
     * Get color class for announcement type.
     */
    private function getTypeColor(string $type): string
    {
        return match ($type) {
            'tausiyah' => 'bg-emerald-100 text-emerald-600',
            'kajian' => 'bg-blue-100 text-blue-600',
            'pengumuman' => 'bg-purple-100 text-purple-600',
            'himbauan' => 'bg-amber-100 text-amber-600',
            'undangan' => 'bg-pink-100 text-pink-600',
            default => 'bg-stone-100 text-stone-600',
        };
    }
}
