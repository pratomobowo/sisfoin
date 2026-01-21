<?php

namespace App\Livewire\Sdm;

use App\Models\Dosen;
use App\Services\SevimaApiService;
use App\Traits\SevimaDataMappingTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class DosenManagement extends Component
{
    use WithPagination, SevimaDataMappingTrait;

    protected $paginationTheme = 'tailwind';

    public $search = '';

    public $perPage = 10;

    public $sortField = 'nama';

    public $sortDirection = 'asc';

    public $filterSatuanKerja = '';

    public $filterStatusAktif = '';

    // Sync state
    public $isSyncing = false;
    public $syncProgress = 0;
    public $syncMessage = '';
    public $syncResults = [];

    // Modal state
    public $showViewModal = false;

    public $viewDosenId;

    public $viewDosen;

    protected $listeners = ['refreshDosens' => '$refresh'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterSatuanKerja()
    {
        $this->resetPage();
    }

    public function updatingFilterStatusAktif()
    {
        $this->resetPage();
    }

    public function updatingFilterJabatanFungsional()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
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

    public function view($id)
    {
        $this->viewDosen = Dosen::findOrFail($id);
        $this->viewDosenId = $id;
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewDosen = null;
        $this->viewDosenId = null;
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filterSatuanKerja = '';
        $this->filterStatusAktif = '';
        $this->resetPage();
    }

    // Pagination Methods
    public function previousPage()
    {
        $this->setPage(max(1, $this->page - 1));
    }

    public function nextPage()
    {
        $this->setPage(min($this->page + 1, $this->dosens->lastPage()));
    }

    public function gotoPage($page)
    {
        $this->setPage($page);
    }

    public function syncSevima()
    {
        $this->isSyncing = true;
        $this->syncProgress = 0;
        $this->syncMessage = 'Memulai sinkronisasi data...';
        $this->syncResults = [];

        try {
            $startTime = microtime(true);
            $sevimaService = new SevimaApiService();

            // Test connection first
            $this->syncMessage = 'Menguji koneksi ke API Sevima...';
            $this->syncProgress = 10;
            
            if (!$sevimaService->testConnection()) {
                throw new Exception('Gagal terhubung ke API Sevima. Periksa koneksi internet dan konfigurasi API.');
            }

            // Sync dosen data
            $this->syncMessage = 'Mengambil data dosen dari Sevima...';
            $this->syncProgress = 30;
            
            $dosenResult = $this->syncDosenData($sevimaService);
            $this->syncProgress = 90;

            // Generate summary
            $summary = $this->generateSyncSummary($dosenResult);
            $summary['duration'] = round(microtime(true) - $startTime, 2);
            
            $this->logSyncResults($summary);
            $this->syncResults = $summary;

            $this->syncMessage = 'Sinkronisasi selesai!';
            $this->syncProgress = 100;

            // Show success message
            $totalInserted = $summary['dosen']['total_inserted'];
            $totalErrors = $summary['dosen']['total_errors'];
            
            if ($totalErrors > 0) {
                session()->flash('warning', "Sinkronisasi selesai dengan {$totalInserted} data berhasil dan {$totalErrors} error. Lihat detail untuk informasi lebih lanjut.");
            } else {
                session()->flash('success', "Sinkronisasi berhasil! {$totalInserted} data berhasil disinkronisasi.");
            }

        } catch (\Throwable $e) {
            // Tangkap semua jenis error termasuk Exception, Error, RuntimeException, dll
            Log::error('Sevima sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->syncMessage = 'Sinkronisasi gagal!';
            $this->syncProgress = 0;
            
            // Tampilkan error yang user-friendly di popup
            $errorMessage = $this->formatUserFriendlyError($e);
            session()->flash('error', $errorMessage);
        } finally {
            $this->isSyncing = false;
        }
    }

    /**
     * Sync dosen data from Sevima API
     */
    private function syncDosenData(SevimaApiService $sevimaService)
    {
        $startTime = microtime(true);
        
        try {
            // Fetch data from API
            $dosenData = $sevimaService->getDosen();
            
            if (!is_array($dosenData)) {
                throw new Exception('Invalid dosen data format received from API');
            }

            $totalApi = count($dosenData);
            
            // Process batch data
            $batchResult = $this->processDosenBatch($dosenData);
            
            // Truncate existing data and insert new data
            DB::transaction(function () use ($batchResult, $sevimaService) {
                // Truncate table
                Dosen::query()->delete();
                
                // Insert new data
                $inserted = 0;
                foreach ($batchResult['processed'] as $dosen) {
                    try {
                        $mappedData = $sevimaService->mapDosenToDosen($dosen);
                        Dosen::create($mappedData);
                        $inserted++;
                    } catch (Exception $e) {
                        Log::error('Failed to insert dosen data', [
                            'error' => $e->getMessage(),
                            'data' => $dosen,
                        ]);
                        $batchResult['errors'][] = "Insert failed: " . $e->getMessage();
                    }
                }
                
                $batchResult['total_inserted'] = $inserted;
                $batchResult['total_updated'] = 0; // Tambahkan ini untuk menghindari undefined array key
            });

            $batchResult['total_api'] = $totalApi;
            $batchResult['duration'] = round(microtime(true) - $startTime, 2);

            return $batchResult;

        } catch (Exception $e) {
            Log::error('Dosen sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate sync summary
     */
    private function generateSyncSummary($dosenResult)
    {
        return [
            'dosen' => [
                'total_api' => $dosenResult['total_api'] ?? 0,
                'total_processed' => $dosenResult['total_processed'] ?? 0,
                'total_inserted' => $dosenResult['total_inserted'] ?? 0,
                'total_updated' => $dosenResult['total_updated'] ?? 0,
                'total_errors' => $dosenResult['total_errors'] ?? 0,
                'errors' => $dosenResult['errors'] ?? [],
            ],
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Log sync results
     */
    private function logSyncResults($summary)
    {
        activity('sevima_sync')
            ->causedBy(auth()->user())
            ->withProperties([
                'sync_results' => $summary,
                'total_dosen_processed' => $summary['dosen']['total_processed'],
                'total_dosen_inserted' => $summary['dosen']['total_inserted'],
                'total_dosen_updated' => $summary['dosen']['total_updated'] ?? 0,
                'total_errors' => $summary['dosen']['total_errors'],
                'duration' => $summary['duration'] ?? 0,
            ])
            ->log('Sevima dosen sync completed');
    }

    /**
     * Format error message to be user-friendly
     */
    private function formatUserFriendlyError($e)
    {
        $errorMessage = $e->getMessage();
        
        // Cek untuk error spesifik dan berikan pesan yang lebih user-friendly
        if (strpos($errorMessage, '403') !== false || strpos($errorMessage, 'Forbidden') !== false) {
            return "Akses ditolak (403 Forbidden). Kemungkinan IP address Anda belum di-whitelist oleh server Sevima. Silakan hubungi admin IT untuk menambahkan IP address ke whitelist.";
        }
        
        if (strpos($errorMessage, '401') !== false || strpos($errorMessage, 'Unauthorized') !== false) {
            return "Autentikasi gagal (401 Unauthorized). Periksa kredensial API Sevima Anda.";
        }
        
        if (strpos($errorMessage, '404') !== false || strpos($errorMessage, 'Not Found') !== false) {
            return "Endpoint API tidak ditemukan (404 Not Found). Periksa konfigurasi URL API Sevima.";
        }
        
        if (strpos($errorMessage, '500') !== false || strpos($errorMessage, 'Internal Server Error') !== false) {
            return "Server error (500 Internal Server Error). Terjadi kesalahan di server Sevima. Silakan coba beberapa saat lagi.";
        }
        
        if (strpos($errorMessage, 'timeout') !== false || strpos($errorMessage, 'Connection timed out') !== false) {
            return "Koneksi timeout. Server Sevima tidak merespon dalam waktu yang ditentukan. Periksa koneksi internet Anda atau coba lagi nanti.";
        }
        
        if (strpos($errorMessage, 'Connection refused') !== false) {
            return "Koneksi ditolak. Server Sevima tidak dapat dijangkau. Periksa apakah server Sevima sedang online atau ada firewall yang memblokir.";
        }
        
        if (strpos($errorMessage, 'cURL error') !== false) {
            return "Error koneksi cURL: " . $errorMessage . ". Periksa koneksi internet dan konfigurasi jaringan Anda.";
        }
        
        if (strpos($errorMessage, 'SSL certificate') !== false || strpos($errorMessage, 'SSL') !== false) {
            return "Error SSL/TLS: " . $errorMessage . ". Terjadi masalah dengan sertifikat keamanan server Sevima.";
        }
        
        if (strpos($errorMessage, 'Gagal terhubung ke API Sevima') !== false) {
            return "Tidak dapat terhubung ke API Sevima. Periksa koneksi internet dan konfigurasi API.";
        }
        
        // Untuk error lainnya, tampilkan pesan yang lebih umum tapi tetap informatif
        if (empty($errorMessage)) {
            return "Terjadi error yang tidak diketahui saat sinkronisasi data. Silakan coba lagi atau hubungi admin IT.";
        }
        
        // Jika error message terlalu panjang, potong untuk tampilan yang lebih baik
        if (strlen($errorMessage) > 200) {
            return "Error: " . substr($errorMessage, 0, 200) . "...";
        }
        
        return "Error: " . $errorMessage;
    }

    public function getSatuanKerjaListProperty()
    {
        return Dosen::whereNotNull('satuan_kerja')
            ->distinct()
            ->pluck('satuan_kerja')
            ->filter()
            ->sort()
            ->values();
    }

    public function render()
    {
        $query = Dosen::query();

        if ($this->search) {
            $query->search($this->search);
        }

        if ($this->filterSatuanKerja) {
            $query->bySatuanKerja($this->filterSatuanKerja);
        }

        if ($this->filterStatusAktif) {
            $query->where('status_aktif', $this->filterStatusAktif);
        }

        $dosens = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.sdm.dosen-management', [
            'dosens' => $dosens,
            'satuanKerjaList' => $this->satuanKerjaList,
        ]);
    }
}
