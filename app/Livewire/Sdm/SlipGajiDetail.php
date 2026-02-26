<?php

namespace App\Livewire\Sdm;

use App\Livewire\Concerns\InteractsWithToast;
use App\Models\SlipGajiHeader;
use App\Services\SlipGajiEmailService;
use App\Services\SlipGajiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class SlipGajiDetail extends Component
{
    use InteractsWithToast, WithPagination;

    public SlipGajiHeader $header;

    public string $search = '';

    public string $sort = 'nama';

    public int $perPage = 10;

    public array $selectedDetails = [];

    public bool $selectAllCheckbox = false;

    public ?int $confirmingEmailSend = null;

    public ?string $confirmingEmployeeName = null;

    public bool $confirmingBulkEmailSend = false;

    public bool $isBulkEmailSending = false;

    public int $page = 1;

    public bool $filterNoEmail = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sort' => ['except' => 'nama'],
        'page' => ['except' => 1],
        'perPage' => ['except' => 10],
        'filterNoEmail' => ['except' => false],
    ];

    protected $listeners = [
        'refreshComponent' => '$refresh',
    ];

    public function mount(SlipGajiHeader $header)
    {
        $this->header = $header;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSort()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedFilterNoEmail()
    {
        $this->resetPage();
    }

    public function updatedSelectAllCheckbox($value)
    {
        if ($value) {
            $slipGajiService = app(SlipGajiService::class);
            $filters = [
                'search' => $this->search,
                'sort' => $this->sort,
                'perPage' => $this->perPage,
                'filterNoEmail' => $this->filterNoEmail,
            ];
            $result = $slipGajiService->getSlipGajiDetails($this->header->id, $filters);

            $this->selectedDetails = $result['details']->pluck('id')->toArray();
        } else {
            $this->selectedDetails = [];
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->sort = 'nama';
        $this->perPage = 10;
        $this->filterNoEmail = false;
        $this->resetPage();
    }

    public function selectAll()
    {
        $slipGajiService = app(SlipGajiService::class);
        $filters = [
            'search' => $this->search,
            'sort' => $this->sort,
            'perPage' => $this->perPage,
            'filterNoEmail' => $this->filterNoEmail,
        ];
        $result = $slipGajiService->getSlipGajiDetails($this->header->id, $filters);

        $this->selectedDetails = $result['details']->pluck('id')->toArray();
        $this->selectAllCheckbox = true;
    }

    public function clearSelection()
    {
        $this->selectedDetails = [];
        $this->selectAllCheckbox = false;
    }

    public function sendBulkEmail()
    {
        if ($this->isBulkEmailSending) {
            $this->toastWarning('Pengiriman email masal sedang diproses. Mohon tunggu hingga selesai.');

            return;
        }

        $this->isBulkEmailSending = true;

        try {
            // Kirim ke semua karyawan tanpa perlu seleksi
            $slipGajiEmailService = app(SlipGajiEmailService::class);
            $result = $slipGajiEmailService->sendBulkEmail($this->header->id, []); // Empty array means send to all

            if ($result['success']) {
                $this->clearSelection();

                $this->toastSuccess($result['message']);

                // Log activity
                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($this->header)
                    ->withProperties([
                        'action' => 'send_bulk_email_all',
                        'valid_recipients' => $result['valid_recipients'],
                        'invalid_recipients' => $result['invalid_recipients'],
                    ])
                    ->log('Kirim bulk email slip gaji ke semua karyawan untuk periode: '.$this->header->periode);

            } else {
                if (($result['level'] ?? 'error') === 'warning') {
                    $this->toastWarning($result['message']);
                } else {
                    $this->toastError($result['message']);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error sending bulk email from Livewire', [
                'header_id' => $this->header->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toastError('Terjadi kesalahan saat mengirim email: '.$e->getMessage());
        } finally {
            $this->isBulkEmailSending = false;
        }
    }

    public function confirmEmailSend($detailId)
    {
        $slipGajiService = app(SlipGajiService::class);
        $detail = $slipGajiService->getSlipGajiDetailById($detailId);

        if ($detail) {
            $this->confirmingEmailSend = $detailId;

            // Get employee name
            if ($detail->resolved_nama) {
                $this->confirmingEmployeeName = $detail->resolved_nama;
            } else {
                $this->confirmingEmployeeName = 'Karyawan';
            }
        }
    }

    public function sendSingleEmail()
    {
        if (! $this->confirmingEmailSend) {
            return;
        }

        $detailId = $this->confirmingEmailSend;

        try {
            $slipGajiEmailService = app(SlipGajiEmailService::class);
            $result = $slipGajiEmailService->sendSingleEmail($detailId);

            if ($result['success']) {
                $this->toastSuccess($result['message']);

                // Log activity
                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($this->header)
                    ->withProperties([
                        'action' => 'send_single_email',
                        'detail_id' => $detailId,
                    ])
                    ->log('Kirim single email slip gaji untuk detail ID: '.$detailId);

            } else {
                if (($result['level'] ?? 'error') === 'warning') {
                    $this->toastWarning($result['message']);
                } else {
                    $this->toastError($result['message']);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error sending single email from Livewire', [
                'header_id' => $this->header->id,
                'detail_id' => $detailId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toastError('Terjadi kesalahan saat mengirim email: '.$e->getMessage());
        }

        // Reset confirmation state
        $this->confirmingEmailSend = null;
        $this->confirmingEmployeeName = null;
    }

    public function cancelEmailSend()
    {
        $this->confirmingEmailSend = null;
        $this->confirmingEmployeeName = null;
    }

    public function confirmBulkEmailSend()
    {
        $this->confirmingBulkEmailSend = true;
    }

    public function sendBulkEmailWithConfirmation()
    {
        $this->confirmingBulkEmailSend = false;
        $this->sendBulkEmail();
    }

    public function cancelBulkEmailSend()
    {
        $this->confirmingBulkEmailSend = false;
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

    public function render()
    {
        $slipGajiService = app(SlipGajiService::class);

        $filters = [
            'search' => $this->search,
            'sort' => $this->sort,
            'perPage' => $this->perPage,
            'filterNoEmail' => $this->filterNoEmail,
        ];

        $result = $slipGajiService->getSlipGajiDetails($this->header->id, $filters);

        $emailStats = app(SlipGajiEmailService::class)->getEmailStats($this->header->id);

        return view('livewire.sdm.slip-gaji-detail', [
            'header' => $result['header'],
            'details' => $result['details'],
            'filters' => $result['filters'],
            'emailStats' => $emailStats,
        ]);
    }
}
