<?php

namespace App\Livewire\Superadmin;

use App\Models\AttendanceLog;
use App\Models\MesinFinger;
use App\Services\FingerprintService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AttendanceLogs extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $page = 1;

    public $search = '';

    public $perPage = 10;

    public $mesinFingerId = '';

    public $date = '';

    public $pin = '';

    public $showDeleteModal = false;

    public $showClearAllModal = false;

    public $showPullDataModal = false;

    public $selectedAttendanceLog = null;

    // Pull data properties
    public $selectedMachines = [];

    public $pullOption = 'all';

    public $pullDateFrom = '';

    public $pullDateTo = '';

    public $autoProcessAttendances = true;

    public $isPullingData = false;

    public $pullProgressMessage = '';

    public $pullResults = [];

    public function render()
    {
        $query = AttendanceLog::with('mesinFinger');

        // Filter berdasarkan mesin finger
        if ($this->mesinFingerId) {
            $query->where('mesin_finger_id', $this->mesinFingerId);
        }

        // Filter berdasarkan tanggal
        if ($this->date) {
            $query->whereDate('datetime', $this->date);
        }

        // Filter berdasarkan PIN atau Nama
        if ($this->pin) {
            $query->where(function ($q) {
                $q->where('pin', 'like', '%'.$this->pin.'%')
                    ->orWhere('name', 'like', '%'.$this->pin.'%')
                    ->orWhereHas('user', function ($qu) {
                        $qu->where('name', 'like', '%'.$this->pin.'%');
                    });
            });
        }

        $attendanceLogs = $query->orderBy('datetime', 'desc')->paginate($this->perPage);
        $mesinFingers = MesinFinger::all();

        return view('livewire.superadmin.attendance-logs', [
            'attendanceLogs' => $attendanceLogs,
            'mesinFingers' => $mesinFingers,
        ]);
    }

    public function resetFilters()
    {
        $this->mesinFingerId = '';
        $this->date = '';
        $this->pin = '';
        $this->perPage = 10;
        $this->resetPage();
    }

    public function confirmDelete(AttendanceLog $attendanceLog)
    {
        $this->selectedAttendanceLog = $attendanceLog;
        $this->showDeleteModal = true;
    }

    public function deleteAttendanceLog()
    {
        if ($this->selectedAttendanceLog) {
            $this->selectedAttendanceLog->delete();
            $this->showDeleteModal = false;
            $this->selectedAttendanceLog = null;

            session()->flash('success', 'Data absensi berhasil dihapus');
        }
    }

    public function confirmClearAll()
    {
        $this->showClearAllModal = true;
    }

    public function clearAllAttendanceLogs()
    {
        AttendanceLog::truncate();
        $this->showClearAllModal = false;

        session()->flash('success', 'Semua data absensi berhasil dihapus');
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
        $this->setPage(max(1, $this->page - 1));
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedMesinFingerId()
    {
        $this->resetPage();
    }

    public function updatedDate()
    {
        $this->resetPage();
    }

    public function updatedPin()
    {
        $this->resetPage();
    }

    /**
     * Pull fingerprint data from ADMS API
     */
    public function pullFingerprintData()
    {
        // Default dates if all is selected
        $dateFrom = $this->pullDateFrom ?: now()->subDays(30)->format('Y-m-d');
        $dateTo = $this->pullDateTo ?: now()->format('Y-m-d');

        // Adjust dates based on option
        if ($this->pullOption === 'today') {
            $dateFrom = now()->format('Y-m-d');
            $dateTo = now()->format('Y-m-d');
        } elseif ($this->pullOption === 'range') {
            if (empty($this->pullDateFrom) || empty($this->pullDateTo)) {
                session()->flash('error', 'Tanggal awal dan tanggal akhir harus diisi');

                return;
            }

            if (strtotime($this->pullDateFrom) > strtotime($this->pullDateTo)) {
                session()->flash('error', 'Tanggal awal tidak boleh lebih besar dari tanggal akhir');

                return;
            }
        }

        $this->isPullingData = true;
        $this->pullResults = [];
        $this->pullProgressMessage = "Tarik data dari ADMS API ({$dateFrom} s/d {$dateTo})...";

        try {
            $fingerprintService = new FingerprintService;

            // Get attendance logs from ADMS API
            $result = $fingerprintService->getAttendanceLogs($dateFrom, $dateTo);

            if (! $result['success']) {
                $message = 'Gagal menarik data: '.$result['message'];
                session()->flash('error', $message);
                $this->dispatch('toast-show', message: $message, type: 'error');

                return;
            }

            if (empty($result['data'])) {
                $message = 'Tidak ada data ditemukan untuk periode tersebut';
                session()->flash('warning', $message);
                $this->dispatch('toast-show', message: $message, type: 'warning');
                $this->showPullDataModal = false;
                $this->resetPullDataForm();

                return;
            }

            // Save to database with auto-processing option
            $saveResult = $fingerprintService->saveAttendanceLogsToDatabase($result['data'], $this->autoProcessAttendances);

            if ($saveResult['success']) {
                $message = "Berhasil menarik data. Disimpan: {$saveResult['saved_count']}, Diperbarui: {$saveResult['updated_count']}.";
                if (isset($saveResult['processed_count']) && $saveResult['processed_count'] > 0) {
                    $message .= " Data absensi karyawan diproses: {$saveResult['processed_count']}.";
                }

                session()->flash('success', $message);
                $this->dispatch('toast-show', message: $message, type: 'success');
                $this->showPullDataModal = false;
                $this->resetPullDataForm();
            } else {
                $message = 'Gagal menyimpan data: '.$saveResult['message'];
                session()->flash('error', $message);
                $this->dispatch('toast-show', message: $message, type: 'error');
            }

        } catch (\Exception $e) {
            \Log::error('Error pulling data from ADMS', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $errorText = strtolower($e->getMessage());
            $isConnectivityIssue = str_contains($errorText, 'connection')
                || str_contains($errorText, 'timed out')
                || str_contains($errorText, 'could not resolve host')
                || str_contains($errorText, 'cURL error')
                || str_contains($errorText, 'network');

            $message = $isConnectivityIssue
                ? 'Gagal terhubung ke server ADMS. Periksa koneksi jaringan atau konfigurasi ADMS.'
                : 'Terjadi kesalahan: '.$e->getMessage();

            session()->flash('error', $message);
            $this->dispatch('toast-show', message: $message, type: 'error');
        } finally {
            $this->isPullingData = false;
            $this->pullProgressMessage = '';
        }
    }

    /**
     * Filter attendance data based on selected option
     */
    private function filterAttendanceData($data)
    {
        if ($this->pullOption === 'all') {
            return $data;
        }

        $filteredData = [];
        $today = now()->format('Y-m-d');

        foreach ($data as $item) {
            $datetime = isset($item['datetime']) ? $item['datetime'] : (isset($item['timestamp']) ? $item['timestamp'] : null);

            if (! $datetime) {
                continue;
            }

            // Convert to date format for comparison
            try {
                $datetimeObj = is_numeric($datetime) ? now()->timestamp($datetime) : new \DateTime($datetime);
                $itemDate = $datetimeObj->format('Y-m-d');
            } catch (\Exception $e) {
                continue;
            }

            switch ($this->pullOption) {
                case 'today':
                    if ($itemDate === $today) {
                        $filteredData[] = $item;
                    }
                    break;

                case 'range':
                    if ($itemDate >= $this->pullDateFrom && $itemDate <= $this->pullDateTo) {
                        $filteredData[] = $item;
                    }
                    break;
            }
        }

        return $filteredData;
    }

    /**
     * Reset pull data form
     */
    private function resetPullDataForm()
    {
        $this->selectedMachines = [];
        $this->pullOption = 'all';
        $this->pullDateFrom = '';
        $this->pullDateTo = '';
        $this->pullResults = [];
    }

    /**
     * Close pull data modal and reset form
     */
    public function closePullDataModal()
    {
        $this->showPullDataModal = false;
        $this->resetPullDataForm();
    }

    /**
     * Select all active machines
     */
    public function selectAllActiveMachines()
    {
        $activeMachines = MesinFinger::where('status', 'active')->pluck('id')->toArray();
        $this->selectedMachines = $activeMachines;
    }

    /**
     * Clear machine selection
     */
    public function clearMachineSelection()
    {
        $this->selectedMachines = [];
    }

    /**
     * Debug method to test modal opening
     */
    public function openPullDataModal()
    {
        try {
            $this->showPullDataModal = true;
            $mesinCount = MesinFinger::count();

            \Log::info('Pull data modal opened', [
                'showPullDataModal' => $this->showPullDataModal,
                'mesinFingers_count' => $mesinCount,
                'selectedMachines' => $this->selectedMachines,
                'pullOption' => $this->pullOption,
            ]);

            // Ensure we have machines to select from
            if ($mesinCount === 0) {
                session()->flash('error', 'Tidak ada mesin fingerprint yang tersedia. Silakan tambahkan mesin fingerprint terlebih dahulu.');
                $this->showPullDataModal = false;

                return;
            }

            // Initialize with default values if empty
            if (empty($this->selectedMachines)) {
                $this->pullOption = 'all';
                $this->pullDateFrom = '';
                $this->pullDateTo = '';
            }

        } catch (\Exception $e) {
            \Log::error('Error opening pull data modal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Terjadi kesalahan saat membuka modal: '.$e->getMessage());
        }
    }

    /**
     * Alternative method to open modal using direct assignment
     */
    public function togglePullDataModal()
    {
        $this->showPullDataModal = ! $this->showPullDataModal;
        if ($this->showPullDataModal) {
            $this->openPullDataModal();
        }
    }
}
