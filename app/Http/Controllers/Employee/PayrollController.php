<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        // Get slip gaji data for current user
        $slipGaji = $this->getSlipGajiData($user);

        return view('staff.penggajian.index', [
            'title' => 'Penggajian',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('staff.dashboard')],
                ['name' => 'Penggajian', 'url' => null],
            ],
            'slipGaji' => $slipGaji,
            'hasSlipGaji' => ! empty($slipGaji),
        ]);
    }

    /**
     * Show slip gaji detail
     */
    public function showSlip($period = null)
    {
        $user = auth()->user();

        if (! $period) {
            $period = now()->format('Y-m');
        }

        $slipGaji = $this->getSlipGajiByPeriod($user, $period);

        if (! $slipGaji) {
            return redirect()->route('staff.penggajian.index')
                ->with('error', 'Slip gaji untuk periode tersebut tidak ditemukan.');
        }

        return view('staff.penggajian.show', [
            'title' => 'Detail Slip Gaji',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('staff.dashboard')],
                ['name' => 'Penggajian', 'url' => route('staff.penggajian.index')],
                ['name' => 'Detail', 'url' => null],
            ],
            'slipGaji' => $slipGaji,
            'period' => $period,
        ]);
    }

    /**
     * Download slip gaji as PDF
     */
    public function downloadPdf($period = null)
    {
        $user = auth()->user();

        if (! $period) {
            $period = now()->format('Y-m');
        }

        $slipGaji = $this->getSlipGajiByPeriod($user, $period);

        if (! $slipGaji) {
            return redirect()->route('staff.penggajian.index')
                ->with('error', 'Slip gaji untuk periode tersebut tidak ditemukan.');
        }

        // For now, return a simple response indicating PDF functionality
        return response()->json([
            'message' => 'PDF download akan segera tersedia',
            'data' => $slipGaji,
        ]);
    }

    /**
     * Get slip gaji data for user (dummy data for now)
     */
    private function getSlipGajiData($user)
    {
        // Generate dummy slip gaji data
        $currentMonth = now()->format('Y-m');
        $lastMonth = now()->subMonth()->format('Y-m');
        $twoMonthsAgo = now()->subMonths(2)->format('Y-m');

        return [
            [
                'period' => $currentMonth,
                'period_name' => now()->format('F Y'),
                'status' => 'Belum Dibuat',
                'status_class' => 'warning',
                'created_at' => null,
                'can_download' => false,
            ],
            [
                'period' => $lastMonth,
                'period_name' => now()->subMonth()->format('F Y'),
                'status' => 'Tersedia',
                'status_class' => 'success',
                'created_at' => now()->subMonth()->endOfMonth(),
                'can_download' => true,
                'gaji_pokok' => 5000000,
                'tunjangan' => 1500000,
                'potongan' => 500000,
                'gaji_bersih' => 6000000,
            ],
            [
                'period' => $twoMonthsAgo,
                'period_name' => now()->subMonths(2)->format('F Y'),
                'status' => 'Tersedia',
                'status_class' => 'success',
                'created_at' => now()->subMonths(2)->endOfMonth(),
                'can_download' => true,
                'gaji_pokok' => 5000000,
                'tunjangan' => 1200000,
                'potongan' => 450000,
                'gaji_bersih' => 5750000,
            ],
        ];
    }

    /**
     * Get slip gaji by specific period
     */
    private function getSlipGajiByPeriod($user, $period)
    {
        $slipGajiList = $this->getSlipGajiData($user);

        foreach ($slipGajiList as $slip) {
            if ($slip['period'] === $period && $slip['status'] === 'Tersedia') {
                return array_merge($slip, [
                    'employee_name' => $user->name,
                    'employee_nip' => $user->nip ?? '123456789',
                    'employee_position' => 'Staff',
                    'employee_department' => 'Teknologi Informasi',

                    // Detailed breakdown
                    'pendapatan' => [
                        'gaji_pokok' => $slip['gaji_pokok'],
                        'tunjangan_jabatan' => 800000,
                        'tunjangan_keluarga' => 400000,
                        'tunjangan_transport' => 300000,
                        'lembur' => 200000,
                    ],
                    'potongan' => [
                        'bpjs_kesehatan' => 150000,
                        'bpjs_ketenagakerjaan' => 100000,
                        'pajak_pph21' => 250000,
                        'lain_lain' => 0,
                    ],
                    'total_pendapatan' => $slip['gaji_pokok'] + $slip['tunjangan'],
                    'total_potongan' => $slip['potongan'],
                    'gaji_bersih' => $slip['gaji_bersih'],
                ]);
            }
        }

        return null;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('employee.payroll.create', [
            'title' => 'Tambah Data Penggajihan',
            'breadcrumbs' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Karyawan', 'url' => route('employee.dashboard')],
                ['name' => 'Penggajihan', 'url' => route('employee.payroll.index')],
                ['name' => 'Tambah', 'url' => null],
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // TODO: Implement payroll creation logic
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
