<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\SlipGajiDetail;
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

        $slipGaji = collect();
        $totalGajiBersih = 0;
        $totalPotongan = 0;
        $totalHonor = 0;
        $totalPajakKurangPotong = 0;
        $availableSlips = 0;
        $totalSlips = 0;
        $slipGajiDetails = null;

        if ($nip) {
            $search = $request->get('search', '');
            $perPage = $request->get('per_page', 10);

            $query = SlipGajiDetail::query()
                ->where('nip', $nip)
                ->with(['header', 'employee', 'dosen'])
                ->orderBy('created_at', 'desc');

            if ($search !== '') {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->whereHas('header', function ($headerQuery) use ($search) {
                        $headerQuery->where('periode', 'like', '%'.$search.'%');
                    });
                });
            }

            $slipGajiDetails = $query->paginate($perPage)->withQueryString();

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

            $allSlips = SlipGajiDetail::query()
                ->where('nip', $nip)
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
}
