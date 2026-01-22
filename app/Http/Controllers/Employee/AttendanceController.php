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
        $user = auth()->user();
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        $realAttendances = \App\Models\Employee\Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();

        $attendances = $realAttendances->map(function ($attendance) {
            return [
                'id' => $attendance->id,
                'date' => $attendance->date->format('Y-m-d'),
                'formatted_date' => $attendance->date->format('d/m/Y'),
                'day_name' => $attendance->date->locale('id')->isoFormat('dddd'),
                'check_in_time' => $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '-',
                'check_out_time' => $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '-',
                'status' => $attendance->status,
                'status_label' => $attendance->status_label,
                'notes' => $attendance->notes,
            ];
        });

        // Calculate summary
        $totalDays = $realAttendances->count();
        $presentDays = $realAttendances->whereIn('status', ['on_time', 'early_arrival', 'present', 'late'])->count();
        $lateDays = $realAttendances->where('status', 'late')->count();
        
        $summary = [
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'late_days' => $lateDays,
            'attendance_percentage' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0,
        ];

        return view('staff.absensi.index', [
            'title' => 'Riwayat Absensi',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Layanan Mandiri', 'url' => null],
                ['name' => 'Absensi', 'url' => null],
            ],
            'attendances' => $attendances,
            'summary' => $summary,
        ]);
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
