<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    /**
     * Display staff payroll page with slip gaji list.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $nip = $user->nip;

        // Initialize default values
        $slipGaji = collect();
        $totalGajiBersih = 0;
        $totalPotongan = 0;
        $totalHonor = 0;
        $totalPajakKurangPotong = 0;
        $availableSlips = 0;
        $totalSlips = 0;
        $slipGajiDetails = null;

        if ($nip) {
            // Get search parameter
            $search = $request->get('search', '');
            $perPage = $request->get('per_page', 10);

            // Get slip gaji data for this NIP
            $query = \App\Models\SlipGajiDetail::where('nip', $nip)
                ->with(['header', 'employee', 'dosen'])
                ->orderBy('created_at', 'desc');

            // Apply search if exists
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('header', function ($subQuery) use ($search) {
                        $subQuery->where('periode', 'like', '%' . $search . '%');
                    });
                });
            }

            $slipGajiDetails = $query->paginate($perPage)->withQueryString();

            // Prepare data for view
            $slipGaji = $slipGajiDetails->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'period' => $detail->header->periode,
                    'period_name' => $detail->header->formatted_periode,
                    'created_at' => $detail->created_at,
                    'gaji_bersih' => $detail->penerimaan_bersih,
                    'total_potongan' => $detail->total_potongan,
                    'total_honor' => ($detail->honor_tetap ?? 0) + ($detail->honor_tunai ?? 0) + ($detail->insentif_golongan ?? 0),
                    'pajak_kurang_potong' => $detail->pph21_kurang_dipotong ?? 0,
                    'can_download' => true,
                    'header_id' => $detail->header_id,
                ];
            });

            // Calculate summary data
            $allSlips = \App\Models\SlipGajiDetail::where('nip', $nip)
                ->with(['header', 'employee', 'dosen'])
                ->get();

            $totalGajiBersih = $allSlips->where('status', 'Tersedia')->sum('penerimaan_bersih');
            $totalPotongan = $allSlips->where('status', 'Tersedia')->sum('total_potongan');
            $totalHonor = $allSlips->where('status', 'Tersedia')->sum(function ($detail) {
                return ($detail->honor_tetap ?? 0) + ($detail->honor_tunai ?? 0) + ($detail->insentif_golongan ?? 0);
            });
            $totalPajakKurangPotong = $allSlips->where('status', 'Tersedia')->sum('pph21_kurang_dipotong');
            $availableSlips = $allSlips->where('status', 'Tersedia')->count();
            $totalSlips = $allSlips->count();
        }

        return view('staff.penggajian', [
            'slipGaji' => $slipGaji,
            'pagination' => $slipGajiDetails,
            'totalGajiBersih' => $totalGajiBersih,
            'totalPotongan' => $totalPotongan,
            'totalHonor' => $totalHonor,
            'totalPajakKurangPotong' => $totalPajakKurangPotong,
            'availableSlips' => $availableSlips,
            'totalSlips' => $totalSlips,
            'search' => $request->get('search', ''),
            'perPage' => $request->get('per_page', 10),
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
