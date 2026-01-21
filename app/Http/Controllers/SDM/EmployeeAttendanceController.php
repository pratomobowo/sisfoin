<?php

namespace App\Http\Controllers\SDM;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class EmployeeAttendanceController extends Controller
{
    /**
     * Display the employee attendance management page.
     */
    public function index(): View
    {
        return view('sdm.employee-attendance.index', [
            'title' => 'Absensi Karyawan',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'SDM', 'url' => route('sdm.dashboard')],
                ['name' => 'Absensi Karyawan', 'url' => null],
            ],
        ]);
    }

    /**
     * Show the form for creating a new employee attendance record.
     */
    public function create(): View
    {
        return view('sdm.employee-attendance.create', [
            'title' => 'Tambah Absensi Manual',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'SDM', 'url' => route('sdm.dashboard')],
                ['name' => 'Absensi Karyawan', 'url' => route('sdm.employee-attendance.index')],
                ['name' => 'Tambah Manual', 'url' => null],
            ],
        ]);
    }

    /**
     * Show the attendance record.
     */
    public function show($id): View
    {
        return view('sdm.employee-attendance.show', [
            'title' => 'Detail Absensi',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'SDM', 'url' => route('sdm.dashboard')],
                ['name' => 'Absensi Karyawan', 'url' => route('sdm.employee-attendance.index')],
                ['name' => 'Detail', 'url' => null],
            ],
        ]);
    }

    /**
     * Show the form for editing the specified attendance record.
     */
    public function edit($id): View
    {
        return view('sdm.employee-attendance.edit', [
            'title' => 'Edit Absensi',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'SDM', 'url' => route('sdm.dashboard')],
                ['name' => 'Absensi Karyawan', 'url' => route('sdm.employee-attendance.index')],
                ['name' => 'Edit', 'url' => null],
            ],
        ]);
    }

    /**
     * Display attendance report page.
     */
    public function report(): View
    {
        return view('sdm.employee-attendance.report', [
            'title' => 'Laporan Absensi',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'SDM', 'url' => route('sdm.dashboard')],
                ['name' => 'Absensi Karyawan', 'url' => route('sdm.employee-attendance.index')],
                ['name' => 'Laporan', 'url' => null],
            ],
        ]);
    }
}