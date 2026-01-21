# Dokumentasi Lengkap Data Dosen

## Daftar Isi
- [Overview](#overview)
- [Struktur Database](#struktur-database)
- [Model Dosen](#model-dosen)
- [Fitur Manajemen Dosen](#fitur-manajemen-dosen)
- [Integrasi Sevima API](#integrasi-sevima-api)
- [Views dan Components](#views-dan-components)
- [Validasi Data](#validasi-data)
- [Contoh Penggunaan](#contoh-penggunaan)
- [Troubleshooting](#troubleshooting)

## Overview

Sistem manajemen data dosen dirancang untuk mengelola informasi lengkap tentang dosen di institusi pendidikan dengan integrasi langsung ke API Sevima. Sistem ini bersifat read-only dari sisi UI dengan data yang disinkronisasi dari sistem eksternal.

### Fitur Utama:
- Sinkronisasi data dari API Sevima
- Pencarian dan filter data dosen
- View detail data lengkap dosen
- Monitoring progress sinkronisasi
- Soft delete untuk data management
- Indexing untuk performa optimal

### Arsitektur Sistem:
- **Data Source**: API Sevima (eksternal)
- **Storage**: Database lokal (tabel `dosens`)
- **UI**: Livewire components dengan Tailwind CSS
- **Sync Mechanism**: Batch processing dengan progress tracking

## Struktur Database

### Tabel `dosens`

Tabel `dosens` menyimpan data lengkap dosen dengan struktur yang sangat komprehensif:

```php
Schema::create('dosens', function (Blueprint $table) {
    $table->id();

    // Identitas dasar
    $table->string('id_pegawai', 20)->nullable();
    $table->string('nip', 50)->nullable();
    $table->string('nip_pns', 50)->nullable();
    $table->string('nidn', 50)->nullable();
    $table->string('nup', 50)->nullable();
    $table->string('nidk', 50)->nullable();
    $table->string('nupn', 50)->nullable();
    $table->string('nik', 50)->nullable();
    $table->string('nama')->nullable();
    $table->string('gelar_depan')->nullable();
    $table->string('gelar_belakang')->nullable();
    $table->enum('jenis_kelamin', ['L', 'P'])->nullable();

    // Agama dan kewarganegaraan
    $table->integer('id_agama')->nullable();
    $table->string('agama')->nullable();
    $table->string('id_kewarganegaraan', 10)->nullable();
    $table->string('kewarganegaraan')->nullable();

    // Data kelahiran
    $table->date('tanggal_lahir')->nullable();
    $table->string('tempat_lahir')->nullable();
    $table->string('status_nikah', 10)->nullable();

    // Alamat domisili
    $table->text('alamat_domisili')->nullable();
    $table->string('rt_domisili', 10)->nullable();
    $table->string('rw_domisili', 10)->nullable();
    $table->string('kode_pos_domisili', 10)->nullable();
    $table->integer('id_kecamatan_domisili')->nullable();
    $table->string('kecamatan_domisili')->nullable();
    $table->integer('id_kota_domisili')->nullable();
    $table->string('kota_domisili')->nullable();
    $table->integer('id_provinsi_domisili')->nullable();
    $table->string('provinsi_domisili')->nullable();

    // Alamat KTP
    $table->text('alamat_ktp')->nullable();
    $table->string('rt_ktp', 10)->nullable();
    $table->string('rw_ktp', 10)->nullable();
    $table->string('kode_pos_ktp', 10)->nullable();
    $table->integer('id_kecamatan_ktp')->nullable();
    $table->string('kecamatan_ktp')->nullable();
    $table->integer('id_kota_ktp')->nullable();
    $table->string('kota_ktp')->nullable();
    $table->integer('id_provinsi_ktp')->nullable();
    $table->string('provinsi_ktp')->nullable();

    // Kontak
    $table->string('nomor_hp', 20)->nullable();
    $table->string('email')->nullable();
    $table->string('email_kampus')->nullable();
    $table->string('telepon', 20)->nullable();
    $table->string('telepon_kantor', 20)->nullable();
    $table->string('telepon_alternatif', 20)->nullable();

    // Unit kerja
    $table->integer('id_satuan_kerja')->nullable();
    $table->string('satuan_kerja')->nullable();
    $table->integer('id_home_base')->nullable();
    $table->string('home_base')->nullable();
    $table->integer('id_pendidikan_terakhir')->nullable();

    // Data kepegawaian
    $table->date('tanggal_masuk')->nullable();
    $table->date('tanggal_sertifikasi_dosen')->nullable();
    $table->string('id_status_aktif', 10)->nullable();
    $table->string('status_aktif', 50)->nullable();
    $table->string('id_status_kepegawaian', 10)->nullable();
    $table->string('status_kepegawaian', 100)->nullable();
    $table->string('id_pangkat', 10)->nullable();
    $table->string('id_jabatan_fungsional', 10)->nullable();
    $table->string('jabatan_fungsional', 100)->nullable();
    $table->string('id_jabatan_sub_fungsional', 10)->nullable();
    $table->string('jabatan_sub_fungsional', 100)->nullable();
    $table->string('id_jabatan_struktural', 10)->nullable();
    $table->string('jabatan_struktural', 100)->nullable();

    // Data sistem
    $table->boolean('is_deleted')->default(false);
    $table->integer('id_sso')->nullable();
    $table->timestamp('api_created_at')->nullable();
    $table->timestamp('api_updated_at')->nullable();
    $table->timestamp('last_synced_at')->nullable();

    $table->timestamps();
    $table->softDeletes();

    // Indexes for performance
    $table->index('nip');
    $table->index('nidn');
    $table->index('nama');
    $table->index('status_aktif');
    $table->index('satuan_kerja');
    $table->index('jabatan_fungsional');
});
```

### Penjelasan Grup Kolom:

#### 1. Identitas Dasar
- `id_pegawai`: ID pegawai di sistem
- `nip`, `nip_pns`: Nomor Induk Pegawai
- `nidn`: Nomor Induk Dosen Nasional
- `nup`, `nidk`, `nupn`: Nomor-nomor identitas lainnya
- `nik`: Nomor Induk Kependudukan
- `nama`: Nama lengkap
- `gelar_depan`, `gelar_belakang`: Gelar akademik
- `jenis_kelamin`: L/P

#### 2. Data Pribadi
- Agama, kewarganegaraan, tanggal lahir, tempat lahir
- Status pernikahan
- Golongan darah

#### 3. Alamat
- **Domisili**: Alamat lengkap, RT/RW, kode pos, kecamatan, kota, provinsi
- **KTP**: Alamat sesuai KTP dengan detail lengkap

#### 4. Kontak
- `nomor_hp`: Nomor handphone
- `email`, `email_kampus`: Email personal dan kampus
- `telepon`, `telepon_kantor`, `telepon_alternatif`: Berbagai nomor telepon

#### 5. Unit Kerja
- `satuan_kerja`: Unit kerja utama
- `home_base`: Fakultas/jurusan home base
- ID terkait untuk relasi database

#### 6. Data Kepegawaian
- `tanggal_masuk`, `tanggal_keluar`: Masa kerja
- `status_aktif`: Status keaktifan dosen
- `status_kepegawaian`: Status kepegawaian
- Jabatan fungsional, sub fungsional, struktural
- Pangkat dan golongan

#### 7. Data Sistem
- `is_deleted`: Flag untuk soft delete
- `id_sso`: ID untuk single sign-on
- Timestamps untuk tracking API dan sinkronisasi

## Model Dosen

Model `Dosen` memiliki fitur-fitur canggih untuk handling data yang kompleks:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dosen extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // Semua kolom di atas dapat diisi massal
        'id_pegawai', 'nip', 'nip_pns', 'nidn', 'nup', 'nidk', 'nupn', 'nik',
        'nama', 'gelar_depan', 'gelar_belakang', 'jenis_kelamin',
        'id_agama', 'agama', 'id_kewarganegaraan', 'kewarganegaraan',
        'golongan_darah', 'tanggal_lahir', 'tempat_lahir', 'status_nikah',
        'alamat_domisili', 'rt_domisili', 'rw_domisili', 'kode_pos_domisili',
        'id_kecamatan_domisili', 'kecamatan_domisili', 'id_kota_domisili', 'kota_domisili',
        'id_provinsi_domisili', 'provinsi_domisili', 'alamat_ktp', 'rt_ktp', 'rw_ktp',
        'kode_pos_ktp', 'id_kecamatan_ktp', 'kecamatan_ktp', 'id_kota_ktp', 'kota_ktp',
        'id_provinsi_ktp', 'provinsi_ktp', 'nomor_hp', 'email', 'email_kampus',
        'telepon', 'telepon_kantor', 'telepon_alternatif', 'id_satuan_kerja',
        'satuan_kerja', 'id_home_base', 'home_base', 'id_pendidikan_terakhir',
        'pendidikan_terakhir', 'jurusan', 'universitas', 'tahun_lulus',
        'tanggal_masuk', 'tanggal_keluar', 'tanggal_sertifikasi_dosen',
        'id_status_aktif', 'status_aktif', 'id_status_kepegawaian', 'status_kepegawaian',
        'jenis_pegawai', 'id_pangkat', 'pangkat', 'id_jabatan_fungsional',
        'jabatan_fungsional', 'id_jabatan_sub_fungsional', 'jabatan_sub_fungsional',
        'id_jabatan_struktural', 'jabatan_struktural', 'nama_bank', 'nomor_rekening',
        'nama_rekening', 'bpjs_kesehatan', 'bpjs_ketenagakerjaan', 'npwp', 'status_pajak',
        'is_deleted', 'id_sso', 'api_created_at', 'api_updated_at', 'last_synced_at'
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_masuk' => 'date',
        'tanggal_keluar' => 'date',
        'tanggal_sertifikasi_dosen' => 'date',
        'api_created_at' => 'datetime',
        'api_updated_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'is_deleted' => 'boolean',
        // Integer casts untuk ID fields
        'id_agama' => 'integer', 'id_kecamatan_domisili' => 'integer',
        'id_kota_domisili' => 'integer', 'id_provinsi_domisili' => 'integer',
        'id_kecamatan_ktp' => 'integer', 'id_kota_ktp' => 'integer',
        'id_provinsi_ktp' => 'integer', 'id_satuan_kerja' => 'integer',
        'id_home_base' => 'integer', 'id_pendidikan_terakhir' => 'integer',
        'id_sso' => 'integer', 'tahun_lulus' => 'integer',
    ];

    // Accessors dan Mutators
    public function getNamaLengkapWithGelarAttribute()
    {
        $nama = $this->nama;
        if ($this->gelar_depan) $nama = $this->gelar_depan.' '.$nama;
        if ($this->gelar_belakang) $nama = $nama.', '.$this->gelar_belakang;
        return $nama;
    }

    public function getStatusTextAttribute()
    {
        return match ($this->status_aktif) {
            'AA', 'Aktif' => 'Aktif',
            'TA', 'Tidak Aktif' => 'Tidak Aktif',
            'M', 'Meninggal Dunia' => 'Meninggal Dunia',
            'PN', 'Pensiun Normal' => 'Pensiun Normal',
            'MD', 'Mengundurkan diri' => 'Mengundurkan diri',
            default => $this->status_aktif ?? 'Tidak Diketahui'
        };
    }

    // Scopes untuk filtering
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('nama', 'like', "%{$search}%")
                ->orWhere('nip', 'like', "%{$search}%")
                ->orWhere('nidn', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('email_kampus', 'like', "%{$search}%")
                ->orWhere('satuan_kerja', 'like', "%{$search}%")
                ->orWhere('jabatan_fungsional', 'like', "%{$search}%")
                ->orWhere('jabatan_struktural', 'like', "%{$search}%");
        });
    }

    public function scopeBySatuanKerja($query, $satuanKerja)
    {
        if ($satuanKerja) {
            return $query->where('satuan_kerja', $satuanKerja);
        }
        return $query;
    }

    // Static methods untuk filter options
    public static function getDistinctSatuanKerja()
    {
        return static::whereNotNull('satuan_kerja')
            ->where('satuan_kerja', '!=', '')
            ->distinct()
            ->pluck('satuan_kerja')
            ->sort()
            ->values();
    }
}
```

### Fitur Model:
- **Soft Deletes**: Data tidak benar-benar dihapus dari database
- **Mass Assignment**: Semua kolom dapat diisi secara massal
- **Type Casting**: Otomatis cast untuk dates, booleans, dan integers
- **Accessors**: Format nama lengkap dengan gelar, status text
- **Scopes**: Pencarian dan filtering yang kompleks
- **Static Methods**: Mendapatkan nilai distinct untuk filter options

## Fitur Manajemen Dosen

### Livewire Component: DosenManagement

Component `DosenManagement` dirancang khusus untuk sistem read-only dengan fokus pada sinkronisasi data dari API Sevima:

```php
<?php

namespace App\Livewire\Sdm;

use App\Models\Dosen;
use App\Services\SevimaApiService;
use App\Traits\SevimaDataMappingTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Livewire\Component;
use Livewire\WithPagination;

class DosenManagement extends Component
{
    use WithPagination, SevimaDataMappingTrait;

    protected $paginationTheme = 'tailwind';

    // Filter properties
    public $search = '';
    public $perPage = 10;
    public $sortField = 'nama';
    public $sortDirection = 'asc';
    public $filterSatuanKerja = '';
    public $filterStatusAktif = '';

    // Sync state properties
    public $isSyncing = false;
    public $syncProgress = 0;
    public $syncMessage = '';
    public $syncResults = [];

    // Modal state
    public $showViewModal = false;
    public $viewDosenId;
    public $viewDosen;

    protected $listeners = ['refreshDosens' => '$refresh'];

    // Filter methods
    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterSatuanKerja() { $this->resetPage(); }
    public function updatingFilterStatusAktif() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    // View modal methods
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

    // Pagination methods
    public function previousPage() { $this->setPage(max(1, $this->page - 1)); }
    public function nextPage() { $this->setPage(min($this->page + 1, $this->dosens->lastPage())); }
    public function gotoPage($page) { $this->setPage($page); }

    /**
     * Main sync method for Sevima API integration
     */
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
            Log::error('Sevima sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->syncMessage = 'Sinkronisasi gagal!';
            $this->syncProgress = 0;
            
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

    public function getSatuanKerjaListProperty()
    {
        return Dosen::whereNotNull('satuan_kerja')
            ->distinct()
            ->pluck('satuan_kerja')
            ->filter()
            ->sort()
            ->values();
    }
}
```

### Fitur Component:
- **Read-Only Interface**: Tidak ada operasi create/update/delete manual
- **Advanced Filtering**: Pencarian, filter satuan kerja, filter status
- **Sevima Integration**: Sinkronisasi data dari API eksternal
- **Progress Tracking**: Real-time progress monitoring
- **Error Handling**: Comprehensive error handling dengan user-friendly messages
- **View Modal**: Detail view lengkap untuk setiap dosen
- **Pagination**: Custom pagination dengan berbagai options

## Integrasi Sevima API

### Overview
Sistem terintegrasi dengan API Sevima untuk mendapatkan data dosen terkini. Integrasi ini menggunakan `SevimaApiService` untuk handling komunikasi dengan API.

### Alur Sinkronisasi:
1. **Test Connection**: Menguji koneksi ke API Sevima
2. **Fetch Data**: Mengambil data dosen dari API
3. **Process Data**: Memproses dan mapping data
4. **Database Transaction**: Truncate tabel lama dan insert data baru
5. **Logging**: Mencatat hasil sinkronisasi

### Error Handling:
Sistem memiliki error handling yang komprehensif untuk berbagai jenis error:
- **Connection Errors**: Timeout, connection refused, SSL errors
- **Authentication Errors**: 401, 403 errors
- **Server Errors**: 500, 404 errors
- **Data Validation Errors**: Invalid data format

### Format Error User-Friendly:
```php
private function formatUserFriendlyError($e)
{
    $errorMessage = $e->getMessage();
    
    if (strpos($errorMessage, '403') !== false) {
        return "Akses ditolak (403 Forbidden). Kemungkinan IP address Anda belum di-whitelist oleh server Sevima.";
    }
    
    if (strpos($errorMessage, 'timeout') !== false) {
        return "Koneksi timeout. Server Sevima tidak merespon dalam waktu yang ditentukan.";
    }
    
    // ... other error formats
}
```

### Monitoring dan Logging:
- **Activity Log**: Semua aktivitas sinkronisasi dicatat
- **Error Logging**: Error detail dicatat untuk debugging
- **Performance Tracking**: Durasi sinkronisasi dicatat
- **Success Rate**: Perhitungan success rate otomatis

## API Endpoints

### Route Definitions
```php
// routes/web.php
Route::middleware(['auth', 'role:sdm|superadmin'])->group(function () {
    Route::get('/sdm/dosens', DosenManagement::class)->name('sdm.dosens.index');
});
```

### Available Operations:
- **GET /sdm/dosens**: Menampilkan halaman manajemen dosen
- **POST /sdm/dosens/sync-sevima**: Sinkronisasi data dari Sevima (via Livewire)
- **GET /sdm/dosens/{id}**: View detail dosen (via Livewire modal)

## Views dan Components

### Main View: `resources/views/sdm/dosens/index.blade.php`

View utama yang sederhana untuk menampilkan Livewire component:

```blade
@extends('layouts.app')

@section('title', 'Manajemen Dosen')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Data Dosen</h3>
                </div>
                <div class="card-body">
                    @livewire('sdm.dosen-management')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

### Livewire View: `resources/views/livewire/sdm/dosen-management.blade.php`

View yang kompleks dengan modern UI menggunakan Tailwind CSS:

```blade
<div class="space-y-6">
    <!-- Header dengan Sync Button -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Data Dosen</h2>
                <p class="mt-1 text-sm text-gray-600">Kelola data dosen dan informasi akademik</p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-2">
                <button wire:click="syncSevima" 
                        wire:loading.attr="disabled"
                        class="bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white px-4 py-2 rounded-lg transition-colors flex items-center space-x-2">
                    <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.582m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <svg wire:loading class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.582m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span wire:loading.remove>Sinkronisasi Data Sevima</span>
                    <span wire:loading>Memproses...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Sync Progress -->
    @if($isSyncing)
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-blue-900">Sinkronisasi Berlangsung</h3>
                <span class="text-sm text-blue-600">{{ $syncProgress }}%</span>
            </div>
            
            <div class="w-full bg-blue-200 rounded-full h-3 mb-4">
                <div class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width: {{ $syncProgress }}%"></div>
            </div>
            
            <p class="text-sm text-blue-700">{{ $syncMessage }}</p>
        </div>
    @endif

    <!-- Sync Results -->
    @if(!empty($syncResults))
        <div class="bg-green-50 border border-green-200 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-green-900">Hasil Sinkronisasi</h3>
                <span class="text-sm text-green-600">Selesai dalam {{ $syncResults['duration'] }} detik</span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Dosen Results -->
                <div class="bg-white rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3">Data Dosen</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total dari API:</span>
                            <span class="font-medium">{{ $syncResults['dosen']['total_api'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Berhasil diproses:</span>
                            <span class="font-medium text-green-600">{{ $syncResults['dosen']['total_processed'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Berhasil disimpan:</span>
                            <span class="font-medium text-green-600">{{ $syncResults['dosen']['total_inserted'] }}</span>
                        </div>
                        @if($syncResults['dosen']['total_errors'] > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Error:</span>
                                <span class="font-medium text-red-600">{{ $syncResults['dosen']['total_errors'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Summary Results -->
                <div class="bg-white rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3">Ringkasan</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total Data:</span>
                            <span class="font-medium">{{ $syncResults['dosen']['total_api'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Success Rate:</span>
                            <span class="font-medium text-green-600">
                                @if($syncResults['dosen']['total_api'] > 0)
                                    {{ round(($syncResults['dosen']['total_inserted'] / $syncResults['dosen']['total_api']) * 100, 1) }}%
                                @else
                                    0%
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Waktu Eksekusi:</span>
                            <span class="font-medium">{{ $syncResults['duration'] }} detik</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Status:</span>
                            <span class="font-medium text-green-600">Selesai</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Error Details -->
            @if(!empty($syncResults['dosen']['errors']))
                <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
                    <h4 class="font-semibold text-red-900 mb-2">Detail Error</h4>
                    <div class="max-h-32 overflow-y-auto space-y-1">
                        @foreach($syncResults['dosen']['errors'] as $error)
                            <p class="text-sm text-red-700">• {{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Advanced Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <!-- Search -->
            <div class="md:col-span-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search"
                           type="text"
                           placeholder="Cari nama, NIP, NIDN, email..."
                           class="w-full pl-10 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Satuan Kerja Filter -->
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Satuan Kerja</label>
                <select wire:model.live="filterSatuanKerja"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Satuan Kerja</option>
                    @foreach($satuanKerjaList as $satuanKerja)
                        <option value="{{ $satuanKerja }}">{{ $satuanKerja }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Aktif Filter -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status Aktif</label>
                <select wire:model.live="filterStatusAktif"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="Aktif">Aktif</option>
                    <option value="Tidak Aktif">Tidak Aktif</option>
                    <option value="Mengundurkan diri">Mengundurkan diri</option>
                    <option value="Kontrak Habis">Kontrak Habis</option>
                    <option value="Pensiun Dini">Pensiun Dini</option>
                </select>
            </div>
            
            <!-- Reset Filter Button -->
            <div class="md:col-span-1 flex items-end">
                <button wire:click="resetFilters"
                        class="w-10 h-10 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition-colors flex items-center justify-center"
                        title="Reset Filter">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.582m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <x-superadmin.flash-messages />

    <!-- Modern Data Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Dosen
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Identitas
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Satuan Kerja
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Jabatan
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($dosens as $dosen)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $dosen->nama_lengkap_with_gelar }}</div>
                                    <div class="text-sm text-gray-500">{{ $dosen->email ?? $dosen->email_kampus }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $dosen->nip ?: '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    @if($dosen->satuan_kerja && strlen($dosen->satuan_kerja) > 12)
                                        {{ substr($dosen->satuan_kerja, 0, 12) }}...
                                    @else
                                        {{ $dosen->satuan_kerja ?: '-' }}
                                    @endif
                                </div>
                                <div class="text-sm text-gray-500">{{ $dosen->home_base ?: '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $dosen->jabatan_fungsional ?: $dosen->jabatan_struktural ?: '-' }}</div>
                                <div class="text-sm text-gray-500">{{ $dosen->status_kepegawaian ?: '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($dosen->status_aktif === 'Aktif') bg-green-100 text-green-800
                                    @elseif($dosen->status_aktif === 'Tidak Aktif') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ $dosen->status_aktif }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end">
                                    <!-- View Button -->
                                    <button wire:click="view({{ $dosen->id }})" 
                                            title="Lihat Detail" aria-label="Lihat Detail"
                                            class="inline-flex p-2 rounded-lg hover:bg-gray-100 text-blue-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                    </svg>
                                    <h3 class="text-sm font-medium text-gray-900 mb-1">Tidak ada data dosen ditemukan</h3>
                                    <p class="text-sm text-gray-500">Coba ubah filter pencarian atau sinkronisasi data dari Sevima</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Custom Pagination -->
        @if($dosens->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-white">
                <x-superadmin.pagination 
                    :currentPage="$dosens->currentPage()"
                    :lastPage="$dosens->lastPage()"
                    :total="$dosens->total()"
                    :perPage="$dosens->perPage()"
                    :showPageInfo="true"
                    :showPerPage="true"
                    :perPageOptions="[10, 25, 50, 100]"
                    :alignment="'justify-between'"
                    perPageWireModel="perPage"
                    previousPageWireModel="previousPage"
                    nextPageWireModel="nextPage"
                    gotoPageWireModel="gotoPage" />
            </div>
        @endif
    </div>

    <!-- Detail View Modal -->
    @if($showViewModal && $viewDosen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeViewModal"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Detail Data Dosen
                            </h3>
                            <button type="button" wire:click="closeViewModal" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="max-h-[70vh] overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Personal Information -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Informasi Personal</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Nama Lengkap:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->nama_lengkap_with_gelar }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">NIP:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->nip ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">NIDN:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->nidn ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">NIK:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->nik ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Jenis Kelamin:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->jenis_kelamin === 'L' ? 'Laki-laki' : ($viewDosen->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Tempat, Tanggal Lahir:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->tempat_lahir ? $viewDosen->tempat_lahir . ', ' : '' }}{{ $viewDosen->tanggal_lahir?->format('d M Y') ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Agama:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->agama ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Status Perkawinan:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->status_nikah ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Kewarganegaraan:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->kewarganegaraan ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Golongan Darah:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->golongan_darah ?: '-' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Academic Information -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Informasi Akademik</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Satuan Kerja:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->satuan_kerja ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Home Base:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->home_base ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Jabatan Fungsional:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->jabatan_fungsional ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Jabatan Struktural:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->jabatan_struktural ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Status Kepegawaian:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->status_kepegawaian ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Jenis Pegawai:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->jenis_pegawai ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Tanggal Masuk:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->tanggal_masuk?->format('d M Y') ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Tanggal Keluar:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->tanggal_keluar?->format('d M Y') ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Status Aktif:</span>
                                            <p class="text-sm text-gray-900">
                                                @if($viewDosen->status_text === 'Aktif')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                        {{ $viewDosen->status_text }}
                                                    </span>
                                                @elseif($viewDosen->status_text === 'Tidak Aktif')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                        {{ $viewDosen->status_text }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        {{ $viewDosen->status_text ?: '-' }}
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Tanggal Sertifikasi:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->tanggal_sertifikasi_dosen?->format('d M Y') ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Pangkat:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->pangkat ?: '-' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Information -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Informasi Kontak</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Email:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->email ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Email Kampus:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->email_kampus ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Telepon:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->telepon ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">HP:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->nomor_hp ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Telepon Kantor:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->telepon_kantor ?: '-' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Address Information -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Alamat</h4>
                                    <div class="space-y-3">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Alamat Domisili:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->alamat_domisili ?: '-' }}</p>
                                            @if($viewDosen->kota_domisili || $viewDosen->provinsi_domisili || $viewDosen->kode_pos_domisili)
                                                <p class="text-xs text-gray-600">
                                                    {{ $viewDosen->kota_domisili }}, {{ $viewDosen->provinsi_domisili }} {{ $viewDosen->kode_pos_domisili }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Education Information -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Pendidikan</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Pendidikan Terakhir:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->pendidikan_terakhir ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Jurusan:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->jurusan ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Universitas:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->universitas ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Tahun Lulus:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->tahun_lulus ?: '-' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Banking Information -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-semibold text-gray-800 mb-3">Informasi Perbankan</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Nama Bank:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->nama_bank ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Nomor Rekening:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->nomor_rekening ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Nama Rekening:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->nama_rekening ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">BPJS Kesehatan:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->bpjs_kesehatan ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">BPJS Ketenagakerjaan:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->bpjs_ketenagakerjaan ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">NPWP:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->npwp ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs font-medium text-gray-500">Status Pajak:</span>
                                            <p class="text-sm text-gray-900">{{ $viewDosen->status_pajak ?: '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="closeViewModal" 
                                class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
```

### Fitur UI:
- **Modern Design**: Menggunakan Tailwind CSS dengan design yang clean dan modern
- **Real-time Sync**: Progress bar dan status sinkronisasi real-time
- **Advanced Filtering**: Multiple filter options dengan live search
- **Responsive Design**: Mobile-friendly dengan responsive layout
- **Detailed Modal**: Comprehensive detail view dengan grouping informasi
- **Custom Pagination**: Pagination component yang fully customizable
- **Status Badges**: Color-coded status badges untuk visual clarity
- **Loading States**: Loading indicators untuk async operations

## Validasi Data

### Validasi Otomatis dari API:
Karena data bersumber dari API Sevima, validasi utama terjadi di sisi API:
- **Data Integrity**: API memastikan data yang dikirim sudah valid
- **Format Validation**: API melakukan validasi format data
- **Business Rules**: API menerapkan business rules yang relevan

### Validasi di Sisi Aplikasi:
- **Type Casting**: Model melakukan type casting otomatis
- **Null Handling**: Aplikasi menangani nilai null dengan aman
- **Format Display**: Accessors memformat data untuk display
- **Search Validation**: Input pencarian divalidasi untuk security

## Contoh Penggunaan

### 1. Sinkronisasi Data dari Sevima
```php
// Melalui UI - klik tombol "Sinkronisasi Data Sevima"
// Otomatis menjalankan proses sinkronisasi dengan progress tracking

// Programmatically (jika diperlukan)
$dosenManagement = new DosenManagement();
$dosenManagement->syncSevima();
```

### 2. Pencarian dan Filtering Data
```php
// Menggunakan model scopes
$dosens = Dosen::search('john')->get(); // Search by name, NIP, NIDN, email
$dosens = Dosen::bySatuanKerja('Fakultas Teknik')->get();
$dosens = Dosen::where('status_aktif', 'Aktif')->get();

// Complex filtering
$dosens = Dosen::search('john')
    ->bySatuanKerja('Fakultas Teknik')
    ->where('status_aktif', 'Aktif')
    ->orderBy('nama', 'asc')
    ->paginate(25);
```

### 3. Mengakses Data dengan Format Khusus
```php
$dosen = Dosen::find(1);

// Nama lengkap dengan gelar
$namaLengkap = $dosen->nama_lengkap_with_gelar; // "Dr. John Doe, M.Kom."

// Status text yang sudah diformat
$statusText = $dosen->status_text; // "Aktif", "Tidak Aktif", dll.

// Status color untuk UI
$statusColor = $dosen->status_color; // CSS class untuk badge
```

### 4. Mendapatkan Options untuk Filter
```php
// Get distinct satuan kerja untuk filter dropdown
$satuanKerjaList = Dosen::getDistinctSatuanKerja();

// Get distinct status aktif
$statusList = Dosen::getDistinctStatusAktif();

// Get distinct jabatan fungsional
$jabatanList = Dosen::getDistinctJabatanFungsional();
```

### 5. Query dengan Performance Optimization
```php
// Menggunakan eager loading (jika ada relasi)
$dosens = Dosen::with(['user', 'satuanKerjaRelation'])->get();

// Menggunakan pagination untuk large datasets
$dosens = Dosen::orderBy('nama')->paginate(50);

// Menggunakan caching untuk frequently accessed data
$activeDosens = Cache::remember('active_dosens', 3600, function () {
    return Dosen::where('status_aktif', 'Aktif')->get();
});
```

## Troubleshooting

### Common Issues dan Solutions:

#### 1. Sinkronisasi Gagal
**Symptoms**: Error message muncul, progress bar stuck
**Solutions**:
- Cek koneksi internet
- Verifikasi API credentials di `.env`
- Pastikan server Sevima accessible
- Cek firewall settings

#### 2. Data Tidak Muncul Setelah Sinkronisasi
**Symptoms**: Sinkronisasi reported success tapi tabel kosong
**Solutions**:
- Cek API response format
- Verifikasi data mapping di `SevimaApiService`
- Check database transaction logs
- Verify `is_deleted` flag handling

#### 3. Pencarian Tidak Berfungsi
**Symptoms**: Search results tidak sesuai expected
**Solutions**:
- Verifikasi search scope di model
- Check database indexes
- Clear browser cache
- Verify Livewire debouncing

#### 4. Filter Dropdown Kosong
**Symptoms**: Satuan kerja atau status dropdown tidak ada options
**Solutions**:
- Check if data exists in database
- Verify distinct query methods
- Check for null/empty values
- Verify permissions

#### 5. Modal Detail Tidak Muncul
**Symptoms**: Klik view button tidak ada response
**Solutions**:
- Check browser console untuk JavaScript errors
- Verify Livewire component state
- Check modal CSS classes
- Verify event listeners

### Debugging Tips:

1. **Enable Debug Mode**: 
```php
// .env file
APP_DEBUG=true
LOG_CHANNEL=daily
```

2. **Check Laravel Logs**:
```bash
tail -f storage/logs/laravel.log
```

3. **Monitor Database Queries**:
```php
// Enable query log
DB::enableQueryLog();
// Run your query
$queries = DB::getQueryLog();
Log::info('Queries', $queries);
```

4. **Browser Developer Tools**:
- Network tab untuk API calls
- Console tab untuk JavaScript errors
- Elements tab untuk DOM inspection

5. **Livewire Debugging**:
```php
// In component
public function mount()
{
    Log::info('Component mounted', ['data' => $this->all()]);
}

// In view
@dump($dosens)
```

### Performance Optimization:

1. **Database Indexes** (sudah ada di migration):
```sql
-- Verify indexes exist
SHOW INDEX FROM dosens;
```

2. **Query Optimization**:
```php
// Use select only needed columns
$dosens = Dosen::select(['id', 'nama', 'nip', 'email', 'status_aktif'])
    ->where('status_aktif', 'Aktif')
    ->get();
```

3. **Pagination Optimization**:
```php
// Use appropriate page size
$dosens = Dosen::paginate(25); // Adjust based on data size
```

4. **Caching Strategy**:
```php
// Cache filter options
$satuanKerjaList = Cache::remember('satuan_kerja_list', 86400, function () {
    return Dosen::getDistinctSatuanKerja();
});
```

5. **Asset Optimization**:
```bash
// Compile and optimize assets
npm run build
```

---

## Changelog

### v1.0.0 (2025-09-25)
- Initial release
- Sevima API integration
- Read-only interface with advanced filtering
- Real-time sync progress monitoring
- Modern responsive UI with Tailwind CSS
- Comprehensive data model with 70+ fields
- Soft delete support
- Performance indexing

---

Dokumentasi ini akan terus diperbarui seiring dengan pengembangan fitur data dosen. Untuk pertanyaan atau masukan, silakan hubungi tim pengembang.
