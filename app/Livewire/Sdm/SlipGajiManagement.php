<?php

namespace App\Livewire\Sdm;

use App\Exports\SlipGajiDataExport;
use App\Exports\SlipGajiTemplateExport;
use App\Livewire\Concerns\InteractsWithToast;
use App\Models\SlipGajiHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class SlipGajiManagement extends Component
{
    use InteractsWithToast, WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';

    public $perPage = 10;

    public $sortField = 'created_at';

    public $sortDirection = 'desc';

    public $filterPeriode = '';

    public $filterTahun = '';

    // Modal states
    public $showDeleteModal = false;

    // Form data
    public $selectedHeader = null;

    protected $listeners = [
        'slipGajiSaved' => 'handleSlipGajiSaved',
        'slipGajiUploaded' => 'handleSlipGajiUploaded',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPeriode()
    {
        $this->resetPage();
    }

    public function updatingTahun()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openDeleteModal($headerId)
    {
        $this->selectedHeader = SlipGajiHeader::find($headerId);
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->selectedHeader = null;
    }

    public function deleteSlipGaji()
    {
        if (! $this->selectedHeader) {
            return;
        }

        try {
            DB::beginTransaction();

            // Delete details first
            $this->selectedHeader->details()->delete();

            // Delete header
            $this->selectedHeader->delete();

            DB::commit();

            $this->closeDeleteModal();

            // Redirect to show toast notification
            return redirect()->route('sdm.slip-gaji.index')
                ->with('success', 'Data slip gaji berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Slip Gaji delete error: '.$e->getMessage());

            // Redirect with error message
            return redirect()->route('sdm.slip-gaji.index')
                ->with('error', 'Gagal menghapus data slip gaji: '.$e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        try {
            return Excel::download(new SlipGajiTemplateExport, 'template_slip_gaji_'.date('Y-m-d').'.xlsx');
        } catch (\Exception $e) {
            Log::error('Download template error: '.$e->getMessage());
            $this->toastError('Gagal mendownload template: '.$e->getMessage());
        }
    }

    public function exportExcel($headerId)
    {
        try {
            $header = SlipGajiHeader::findOrFail($headerId);
            $filename = 'data_slip_gaji_'.str_replace(' ', '_', $header->periode).'_'.date('YmdHis').'.xlsx';

            return Excel::download(new SlipGajiDataExport($headerId), $filename);
        } catch (\Exception $e) {
            Log::error('Export Excel error: '.$e->getMessage());
            $this->toastError('Gagal mengekspor data ke Excel: '.$e->getMessage());
        }
    }

    public function getHeadersProperty()
    {
        try {
            $query = SlipGajiHeader::with(['uploader', 'details'])
                ->withCount('details');

            // Search functionality
            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('periode', 'like', '%'.$this->search.'%')
                        ->orWhere('file_original', 'like', '%'.$this->search.'%');
                });
            }

            // Filter by periode
            if ($this->filterPeriode) {
                $query->where('periode', 'like', $this->filterPeriode.'%');
            }

            // Filter by tahun
            if ($this->filterTahun) {
                $query->where('periode', 'like', $this->filterTahun.'%');
            }

            // Sorting
            $query->orderBy($this->sortField, $this->sortDirection);

            return $query->paginate($this->perPage);
        } catch (\Exception $e) {
            Log::error('SlipGajiManagement render error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage);
        }
    }

    public function getAvailablePeriodesProperty()
    {
        return SlipGajiHeader::select('periode')
            ->distinct()
            ->orderBy('periode', 'desc')
            ->pluck('periode');
    }

    public function getAvailableTahunsProperty()
    {
        return SlipGajiHeader::selectRaw('SUBSTRING(periode, 1, 4) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');
    }

    public function render()
    {
        try {
            Log::info('SlipGajiManagement render success', [
                'headers_count' => $this->headers->count(),
                'headers_total' => $this->headers->total(),
                'first_header' => $this->headers->first() ? [
                    'id' => $this->headers->first()->id,
                    'periode' => $this->headers->first()->periode,
                    'file_original' => $this->headers->first()->file_original,
                    'uploaded_by' => $this->headers->first()->uploaded_by,
                    'uploaded_at' => $this->headers->first()->uploaded_at,
                    'details_count' => $this->headers->first()->details_count,
                    'uploader' => $this->headers->first()->uploader ? [
                        'id' => $this->headers->first()->uploader->id,
                        'name' => $this->headers->first()->uploader->name,
                        'email' => $this->headers->first()->uploader->email,
                    ] : null,
                ] : null,
            ]);

            return view('livewire.sdm.slip-gaji-management', [
                'headers' => $this->headers,
                'availablePeriodes' => $this->availablePeriodes,
                'availableTahuns' => $this->availableTahuns,
            ]);

        } catch (\Exception $e) {
            Log::error('SlipGajiManagement render error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return view('livewire.sdm.slip-gaji-management', [
                'headers' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage),
                'availablePeriodes' => collect([]),
                'availableTahuns' => collect([]),
            ]);
        }
    }

    // Pagination methods
    public function gotoPage($page)
    {
        $this->setPage($page);
    }

    public function nextPage()
    {
        $this->setPage($this->page + 1);
    }

    public function previousPage()
    {
        $this->setPage($this->page - 1);
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filterPeriode = '';
        $this->filterTahun = '';
        $this->resetPage();
    }

    /**
     * Handle slip gaji saved event
     */
    public function handleSlipGajiSaved()
    {
        $this->toastSuccess('Data slip gaji berhasil disimpan');
        $this->resetPage();
    }

    /**
     * Handle slip gaji uploaded event
     */
    public function handleSlipGajiUploaded()
    {
        $this->toastSuccess('Data slip gaji berhasil diupload');
        $this->resetPage();
    }
}
