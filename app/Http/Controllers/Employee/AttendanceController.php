<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendance records.
     */
    public function index(): View
    {
        // Generate dummy attendance data
        $attendanceData = $this->generateDummyAttendanceData();

        return view('staff.absensi.index', [
            'title' => 'Data Absensi',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Staff', 'url' => route('staff.dashboard')],
                ['name' => 'Absensi', 'url' => null],
            ],
            'attendances' => $attendanceData['attendances'],
            'summary' => $attendanceData['summary'],
        ]);
    }

    /**
     * Generate dummy attendance data for display
     */
    private function generateDummyAttendanceData(): array
    {
        $attendances = [];
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $daysInMonth = now()->daysInMonth;

        // Generate attendance for current month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = now()->setDay($day);

            // Skip weekends for some variety
            if ($date->isWeekend() && rand(0, 100) > 30) {
                continue;
            }

            $checkInTime = $date->copy()->setTime(8, rand(0, 30), rand(0, 59));
            $checkOutTime = $date->copy()->setTime(17, rand(0, 30), rand(0, 59));

            // Add some overtime randomly
            if (rand(0, 100) > 70) {
                $checkOutTime->addHours(rand(1, 3));
            }

            $totalHours = $checkOutTime->diffInHours($checkInTime);
            $overtimeHours = max(0, $totalHours - 8);

            $status = 'present';
            if ($checkInTime->hour > 8 || ($checkInTime->hour == 8 && $checkInTime->minute > 15)) {
                $status = 'late';
            }

            $attendances[] = [
                'id' => $day,
                'date' => $date->format('Y-m-d'),
                'formatted_date' => $date->format('d/m/Y'),
                'day_name' => $date->format('l'),
                'check_in_time' => $checkInTime->format('H:i'),
                'check_out_time' => $checkOutTime->format('H:i'),
                'total_hours' => $totalHours,
                'overtime_hours' => $overtimeHours,
                'status' => $status,
                'status_label' => $this->getStatusLabel($status),
                'status_badge' => $this->getStatusBadge($status),
                'notes' => $overtimeHours > 0 ? 'Lembur '.$overtimeHours.' jam' : null,
            ];
        }

        // Calculate summary
        $totalDays = count($attendances);
        $presentDays = count(array_filter($attendances, fn ($a) => in_array($a['status'], ['present', 'late'])));
        $lateDays = count(array_filter($attendances, fn ($a) => $a['status'] === 'late'));
        $totalOvertime = array_sum(array_column($attendances, 'overtime_hours'));

        return [
            'attendances' => collect($attendances)->sortByDesc('date'),
            'summary' => [
                'total_days' => $totalDays,
                'present_days' => $presentDays,
                'late_days' => $lateDays,
                'total_overtime' => $totalOvertime,
                'attendance_percentage' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0,
            ],
        ];
    }

    /**
     * Get status label
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'absent' => 'Tidak Hadir',
            'half_day' => 'Setengah Hari',
            'sick' => 'Sakit',
            'leave' => 'Cuti',
            default => 'Unknown'
        };
    }

    /**
     * Get status badge class
     */
    private function getStatusBadge(string $status): string
    {
        return match ($status) {
            'present' => 'bg-success text-white',
            'late' => 'bg-warning text-dark',
            'absent' => 'bg-danger text-white',
            'half_day' => 'bg-info text-white',
            'sick' => 'bg-secondary text-white',
            'leave' => 'bg-primary text-white',
            default => 'bg-secondary text-white'
        };
    }

    /**
     * Display the check-in/check-out form.
     */
    public function checkin(): View
    {
        return view('employee.attendance.checkin', [
            'title' => 'Check In/Out',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Karyawan', 'url' => route('employee.dashboard')],
                ['name' => 'Absensi', 'url' => route('employee.attendance.index')],
                ['name' => 'Check In/Out', 'url' => null],
            ],
        ]);
    }

    /**
     * Process check-in.
     */
    public function processCheckin(Request $request): JsonResponse
    {
        // TODO: Implement check-in logic
        return response()->json([
            'success' => true,
            'message' => 'Check-in berhasil dicatat.',
            'time' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Process check-out.
     */
    public function processCheckout(Request $request): JsonResponse
    {
        // TODO: Implement check-out logic
        return response()->json([
            'success' => true,
            'message' => 'Check-out berhasil dicatat.',
            'time' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Display attendance history.
     */
    public function history(): View
    {
        return view('employee.attendance.history', [
            'title' => 'Riwayat Absensi',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Karyawan', 'url' => route('employee.dashboard')],
                ['name' => 'Absensi', 'url' => route('employee.attendance.index')],
                ['name' => 'Riwayat', 'url' => null],
            ],
        ]);
    }

    /**
     * Generate attendance report.
     */
    public function report(): View
    {
        return view('employee.attendance.report', [
            'title' => 'Laporan Absensi',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Karyawan', 'url' => route('employee.dashboard')],
                ['name' => 'Absensi', 'url' => route('employee.attendance.index')],
                ['name' => 'Laporan', 'url' => null],
            ],
        ]);
    }

    /**
     * Show the form for creating a manual attendance record.
     */
    public function create(): View
    {
        return view('employee.attendance.create', [
            'title' => 'Tambah Absensi Manual',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Karyawan', 'url' => route('employee.dashboard')],
                ['name' => 'Absensi', 'url' => route('employee.attendance.index')],
                ['name' => 'Tambah', 'url' => null],
            ],
        ]);
    }

    /**
     * Store a manually created attendance record.
     */
    public function store(Request $request): RedirectResponse
    {
        // TODO: Implement manual attendance creation logic
        return redirect()->route('employee.attendance.index')
            ->with('success', 'Data absensi berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified attendance record.
     */
    public function edit(string $id): View
    {
        return view('employee.attendance.edit', [
            'title' => 'Edit Data Absensi',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Karyawan', 'url' => route('employee.dashboard')],
                ['name' => 'Absensi', 'url' => route('employee.attendance.index')],
                ['name' => 'Edit', 'url' => null],
            ],
        ]);
    }

    /**
     * Update the specified attendance record.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        // TODO: Implement attendance update logic
        return redirect()->route('employee.attendance.index')
            ->with('success', 'Data absensi berhasil diperbarui.');
    }

    /**
     * Remove the specified attendance record.
     */
    public function destroy(string $id): RedirectResponse
    {
        // TODO: Implement attendance deletion logic
        return redirect()->route('employee.attendance.index')
            ->with('success', 'Data absensi berhasil dihapus.');
    }
}
