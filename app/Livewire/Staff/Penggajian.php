<?php

namespace App\Livewire\Staff;

use App\Models\SlipGajiDetail;
use Livewire\Component;
use Livewire\WithPagination;

class Penggajian extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';

    public $perPage = 10;

    public $page = 1;

    // Summary data
    public $totalGajiBersih = 0;

    public $totalPotongan = 0;

    public $totalHonor = 0;

    public $totalPajakKurangPotong = 0;

    public $availableSlips = 0;

    public $totalSlips = 0;

    protected $listeners = ['downloadPdf'];

    public function mount()
    {
        $this->loadSummaryData();
    }

    public function render()
    {
        $user = auth()->user();
        $nip = $user->nip;

        if (! $nip) {
            return view('livewire.staff.penggajian', [
                'slipGaji' => collect(),
            ]);
        }

        // Get slip gaji data for this NIP
        $query = SlipGajiDetail::where('nip', $nip)
            ->whereHas('header', function ($query) {
                $query->where('status', 'published');
            })
            ->with(['header', 'employee', 'dosen'])
            ->orderBy('created_at', 'desc');

        // Apply search if exists
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('header', function ($subQuery) {
                    $subQuery->where('periode', 'like', '%'.$this->search.'%');
                });
            });
        }

        $slipGajiDetails = $query->paginate($this->perPage);

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
                'can_download' => true, // Staff selalu bisa melihat dan download slip gaji mereka
                'header_id' => $detail->header_id,
            ];
        });

        return view('livewire.staff.penggajian', [
            'slipGaji' => $slipGaji,
            'pagination' => $slipGajiDetails,
        ]);
    }

    public function loadSummaryData()
    {
        $user = auth()->user();
        $nip = $user->nip;

        if (! $nip) {
            return;
        }

        // Get all slip gaji for this NIP
        $slipGajiDetails = SlipGajiDetail::where('nip', $nip)
            ->whereHas('header', function ($query) {
                $query->where('status', 'published');
            })
            ->with(['header', 'employee', 'dosen'])
            ->get();

        // Calculate summary data
        $this->totalGajiBersih = $slipGajiDetails->where('status', 'Tersedia')->sum('penerimaan_bersih');
        $this->totalPotongan = $slipGajiDetails->where('status', 'Tersedia')->sum('total_potongan');
        $this->totalHonor = $slipGajiDetails->where('status', 'Tersedia')->sum(function ($detail) {
            return ($detail->honor_tetap ?? 0) + ($detail->honor_tunai ?? 0) + ($detail->insentif_golongan ?? 0);
        });
        $this->totalPajakKurangPotong = $slipGajiDetails->where('status', 'Tersedia')->sum('pph21_kurang_dipotong');

        $this->availableSlips = $slipGajiDetails->where('status', 'Tersedia')->count();
        $this->totalSlips = $slipGajiDetails->count();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->perPage = 10;
        $this->resetPage();
    }

    public function nextPage()
    {
        $this->setPage($this->page + 1);
    }

    public function previousPage()
    {
        $this->setPage(max(1, $this->page - 1));
    }

    public function gotoPage($page)
    {
        $this->setPage($page);
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    // Method to refresh data (called from other components)
    public function refreshData()
    {
        $this->loadSummaryData();
        $this->resetPage();
    }

    public function downloadPdf($headerId)
    {
        $user = Auth::user();
        $nip = $user->nip;

        if (! $nip) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Data NIP tidak ditemukan',
            ]);

            return;
        }

        $detail = SlipGajiDetail::where('header_id', $headerId)
            ->where('nip', $nip)
            ->with(['header', 'employee', 'dosen'])
            ->first();

        if (! $detail) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Slip gaji tidak ditemukan',
            ]);

            return;
        }

        try {
            $slipGajiService = app(SlipGajiService::class);
            $pdfContent = $slipGajiService->generatePdfSlip($detail->id);
            $filename = $slipGajiService->generatePdfFilename($detail);

            return response()->streamDownload(function () use ($pdfContent) {
                echo $pdfContent;
            }, $filename, ['Content-Type' => 'application/pdf']);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Gagal mengunduh slip gaji: '.$e->getMessage(),
            ]);
        }
    }
}
