<?php

namespace App\Livewire\Sdm;

use App\Models\Dosen;
use App\Models\Employee;
use App\Models\SyncRun;
use App\Models\User;
use App\Services\SevimaApiService;
use App\Services\Sync\SyncOrchestratorService;
use App\Traits\SevimaDataMappingTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class EmployeeManagement extends Component
{
    use SevimaDataMappingTrait, WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';

    public $perPage = 10;

    public $sortField = 'nama';

    public $sortDirection = 'asc';

    public $filterUnitKerja = '';

    public $filterStatusAktif = '';

    // Sync state
    public $isSyncing = false;

    public $syncProgress = 0;

    public $syncMessage = '';

    public $syncResults = [];

    public function mount(): void
    {
        if (session()->has('success')) {
            $this->syncMessage = (string) session('success');
            $this->syncProgress = 100;
        } elseif (session()->has('warning')) {
            $this->syncMessage = (string) session('warning');
            $this->syncProgress = 100;
        } elseif (session()->has('error')) {
            $this->syncMessage = (string) session('error');
            $this->syncProgress = 0;
        }
    }

    // Modal state
    public $showViewModal = false;

    public $showEditModal = false;

    public $viewEmployeeId;

    public $viewEmployee;

    // Form fields for create/edit
    public $employee_id;

    public $id_pegawai;

    public $nik;

    public $nip;

    public $nip_pns;

    public $nidn;

    public $nup;

    public $nidk;

    public $nupn;

    public $nama;

    public $gelar_depan;

    public $gelar_belakang;

    public $jenis_kelamin;

    public $id_agama;

    public $agama;

    public $id_kewarganegaraan;

    public $kewarganegaraan;

    public $tanggal_lahir;

    public $tempat_lahir;

    public $status_nikah;

    public $alamat_ktp;

    public $rt_ktp;

    public $rw_ktp;

    public $kelurahan_ktp;

    public $kecamatan_ktp;

    public $kota_ktp;

    public $provinsi_ktp;

    public $kode_pos_ktp;

    public $alamat_domisili;

    public $rt_domisili;

    public $rw_domisili;

    public $kelurahan_domisili;

    public $kecamatan_domisili;

    public $kota_domisili;

    public $provinsi_domisili;

    public $kode_pos_domisili;

    public $telepon;

    public $telepon_kantor;

    public $telepon_alternatif;

    public $hp;

    public $email;

    public $email_kampus;

    public $id_satuan_kerja;

    public $satuan_kerja;

    public $id_home_base;

    public $home_base;

    public $id_pendidikan_terakhir;

    public $tanggal_masuk;

    public $tanggal_sertifikasi_dosen;

    public $id_status_aktif;

    public $status_aktif;

    public $id_status_kepegawaian;

    public $status_kepegawaian;

    public $id_pangkat;

    public $id_jabatan_fungsional;

    public $jabatan_fungsional;

    public $id_jabatan_sub_fungsional;

    public $jabatan_sub_fungsional;

    public $id_jabatan_struktural;

    public $jabatan_struktural;

    public $is_deleted;

    public $id_sso;

    public $last_sync_at;

    public $fingerprint_pin;

    protected $listeners = ['refreshEmployees' => '$refresh'];

    protected function rules()
    {
        return [
            'nik' => 'nullable|string|max:255',
            'nip' => 'nullable|string|max:255',
            'nip_pns' => 'nullable|string|max:255',
            'nidn' => 'nullable|string|max:255',
            'nup' => 'nullable|string|max:255',
            'nidk' => 'nullable|string|max:255',
            'nupn' => 'nullable|string|max:255',
            'nama' => 'nullable|string|max:255',
            'gelar_depan' => 'nullable|string|max:255',
            'gelar_belakang' => 'nullable|string|max:255',
            'jenis_kelamin' => 'nullable|string|max:10',
            'id_agama' => 'nullable|string|max:255',
            'agama' => 'nullable|string|max:255',
            'id_kewarganegaraan' => 'nullable|string|max:255',
            'kewarganegaraan' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|string|max:255',
            'tempat_lahir' => 'nullable|string|max:255',
            'status_nikah' => 'nullable|string|max:10',
            'alamat_ktp' => 'nullable|string',
            'rt_ktp' => 'nullable|string|max:255',
            'rw_ktp' => 'nullable|string|max:255',
            'kelurahan_ktp' => 'nullable|string|max:255',
            'kecamatan_ktp' => 'nullable|string|max:255',
            'kota_ktp' => 'nullable|string|max:255',
            'provinsi_ktp' => 'nullable|string|max:255',
            'kode_pos_ktp' => 'nullable|string|max:255',
            'alamat_domisili' => 'nullable|string',
            'rt_domisili' => 'nullable|string|max:255',
            'rw_domisili' => 'nullable|string|max:255',
            'kelurahan_domisili' => 'nullable|string|max:255',
            'kecamatan_domisili' => 'nullable|string|max:255',
            'kota_domisili' => 'nullable|string|max:255',
            'provinsi_domisili' => 'nullable|string|max:255',
            'kode_pos_domisili' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:255',
            'telepon_kantor' => 'nullable|string|max:255',
            'telepon_alternatif' => 'nullable|string|max:255',
            'hp' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'email_kampus' => 'nullable|email|max:255',
            'id_satuan_kerja' => 'nullable|string|max:255',
            'satuan_kerja' => 'nullable|string|max:255',
            'id_home_base' => 'nullable|string|max:255',
            'home_base' => 'nullable|string|max:255',
            'id_pendidikan_terakhir' => 'nullable|string|max:255',
            'tanggal_masuk' => 'nullable|string|max:255',
            'tanggal_sertifikasi_dosen' => 'nullable|string|max:255',
            'id_status_aktif' => 'nullable|string|max:255',
            'status_aktif' => 'nullable|string|max:255',
            'id_status_kepegawaian' => 'nullable|string|max:255',
            'status_kepegawaian' => 'nullable|string|max:255',
            'id_pangkat' => 'nullable|string|max:255',
            'id_jabatan_fungsional' => 'nullable|string|max:255',
            'jabatan_fungsional' => 'nullable|string|max:255',
            'id_jabatan_sub_fungsional' => 'nullable|string|max:255',
            'jabatan_sub_fungsional' => 'nullable|string|max:255',
            'id_jabatan_struktural' => 'nullable|string|max:255',
            'jabatan_struktural' => 'nullable|string|max:255',
            'is_deleted' => 'nullable|string|max:10',
            'id_sso' => 'nullable|string|max:255',
            'last_sync_at' => 'nullable|date',
            'fingerprint_pin' => 'nullable|string|max:255',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterUnitKerja()
    {
        $this->resetPage();
    }

    public function updatingFilterStatusAktif()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function syncSevima()
    {
        $this->isSyncing = true;
        $this->syncProgress = 5;
        $this->syncMessage = 'Menjadwalkan sinkronisasi asinkron...';
        $this->syncResults = [];

        try {
            $run = app(SyncOrchestratorService::class)->start('all', auth()->id(), 'employee-management');

            $this->syncProgress = 100;
            $this->syncMessage = 'Sinkronisasi berhasil dijadwalkan.';

            if ($run->status === 'failed') {
                session()->flash('warning', 'Sinkronisasi tidak dijadwalkan: proses untuk mode ini sedang berjalan.');
            } else {
                session()->flash('success', 'Sinkronisasi berjalan di background. Silakan refresh beberapa saat lagi untuk melihat hasil.');
            }
        } catch (\Throwable $e) {
            Log::error('Failed to schedule SDM sync', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->syncProgress = 0;
            $this->syncMessage = 'Gagal menjadwalkan sinkronisasi.';
            session()->flash('error', $this->formatUserFriendlyError($e));
        } finally {
            $this->isSyncing = false;
        }
    }

    /**
     * Sync pegawai data from Sevima API
     */
    private function syncPegawaiData(SevimaApiService $sevimaService)
    {
        $startTime = microtime(true);

        try {
            // Fetch data from API
            $pegawaiData = $sevimaService->getPegawai();

            if (! is_array($pegawaiData)) {
                throw new Exception('Invalid pegawai data format received from API');
            }

            $totalApi = count($pegawaiData);

            // Process batch data
            $batchResult = $this->processPegawaiBatch($pegawaiData);

            // Truncate existing data and insert new data
            DB::transaction(function () use ($batchResult, $sevimaService) {
                // Truncate table
                Employee::query()->delete();

                // Insert new data
                $inserted = 0;
                foreach ($batchResult['processed'] as $pegawai) {
                    try {
                        $mappedData = $sevimaService->mapPegawaiToEmployee($pegawai);
                        $employee = Employee::create($mappedData);
                        $this->relinkEmployeeUsersByMasterId($employee);
                        $inserted++;
                    } catch (Exception $e) {
                        Log::error('Failed to insert pegawai data', [
                            'error' => $e->getMessage(),
                            'data' => $pegawai,
                        ]);
                        $batchResult['errors'][] = 'Insert failed: '.$e->getMessage();
                    }
                }

                $batchResult['total_inserted'] = $inserted;
            });

            $batchResult['total_api'] = $totalApi;
            $batchResult['duration'] = round(microtime(true) - $startTime, 2);

            return $batchResult;

        } catch (Exception $e) {
            Log::error('Pegawai sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
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

            if (! is_array($dosenData)) {
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
                        $dosenRecord = Dosen::create($mappedData);
                        $this->relinkDosenUsersByMasterId($dosenRecord);
                        $inserted++;
                    } catch (Exception $e) {
                        Log::error('Failed to insert dosen data', [
                            'error' => $e->getMessage(),
                            'data' => $dosen,
                        ]);
                        $batchResult['errors'][] = 'Insert failed: '.$e->getMessage();
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

    private function relinkEmployeeUsersByMasterId(Employee $employee): void
    {
        if (empty($employee->id_pegawai)) {
            return;
        }

        $historicalIds = Employee::withTrashed()
            ->where('id_pegawai', $employee->id_pegawai)
            ->pluck('id');

        if ($historicalIds->isEmpty()) {
            return;
        }

        User::where('employee_type', 'employee')
            ->whereIn('employee_id', $historicalIds)
            ->update(['employee_id' => $employee->id]);
    }

    private function relinkDosenUsersByMasterId(Dosen $dosen): void
    {
        if (empty($dosen->id_pegawai)) {
            return;
        }

        $historicalIds = Dosen::withTrashed()
            ->where('id_pegawai', $dosen->id_pegawai)
            ->pluck('id');

        if ($historicalIds->isEmpty()) {
            return;
        }

        User::where('employee_type', 'dosen')
            ->whereIn('employee_id', $historicalIds)
            ->update(['employee_id' => $dosen->id]);
    }

    public function view($id)
    {
        $this->viewEmployee = Employee::findOrFail($id);
        $this->viewEmployeeId = $id;
        $this->showViewModal = true;
    }

    public function create()
    {
        $this->resetInputFields();
        $this->showEditModal = true;
    }

    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        $this->employee_id = $employee->id;
        $this->id_pegawai = $employee->id_pegawai;
        $this->nik = $employee->nik;
        $this->nip = $employee->nip;
        $this->nip_pns = $employee->nip_pns;
        $this->nidn = $employee->nidn;
        $this->nup = $employee->nup;
        $this->nidk = $employee->nidk;
        $this->nupn = $employee->nupn;
        $this->nama = $employee->nama;
        $this->gelar_depan = $employee->gelar_depan;
        $this->gelar_belakang = $employee->gelar_belakang;
        $this->jenis_kelamin = $employee->jenis_kelamin;
        $this->id_agama = $employee->id_agama;
        $this->agama = $employee->agama;
        $this->id_kewarganegaraan = $employee->id_kewarganegaraan;
        $this->kewarganegaraan = $employee->kewarganegaraan;
        $this->tanggal_lahir = $employee->tanggal_lahir;
        $this->tempat_lahir = $employee->tempat_lahir;
        $this->status_nikah = $employee->status_nikah;
        $this->alamat_ktp = $employee->alamat_ktp;
        $this->rt_ktp = $employee->rt_ktp;
        $this->rw_ktp = $employee->rw_ktp;
        $this->kelurahan_ktp = $employee->kelurahan_ktp;
        $this->kecamatan_ktp = $employee->kecamatan_ktp;
        $this->kota_ktp = $employee->kota_ktp;
        $this->provinsi_ktp = $employee->provinsi_ktp;
        $this->kode_pos_ktp = $employee->kode_pos_ktp;
        $this->alamat_domisili = $employee->alamat_domisili;
        $this->rt_domisili = $employee->rt_domisili;
        $this->rw_domisili = $employee->rw_domisili;
        $this->kelurahan_domisili = $employee->kelurahan_domisili;
        $this->kecamatan_domisili = $employee->kecamatan_domisili;
        $this->kota_domisili = $employee->kota_domisili;
        $this->provinsi_domisili = $employee->provinsi_domisili;
        $this->kode_pos_domisili = $employee->kode_pos_domisili;
        $this->telepon = $employee->telepon;
        $this->telepon_kantor = $employee->telepon_kantor;
        $this->telepon_alternatif = $employee->telepon_alternatif;
        $this->hp = $employee->hp;
        $this->email = $employee->email;
        $this->email_kampus = $employee->email_kampus;
        $this->id_satuan_kerja = $employee->id_satuan_kerja;
        $this->satuan_kerja = $employee->satuan_kerja;
        $this->id_home_base = $employee->id_home_base;
        $this->home_base = $employee->home_base;
        $this->id_pendidikan_terakhir = $employee->id_pendidikan_terakhir;
        $this->tanggal_masuk = $employee->tanggal_masuk;
        $this->tanggal_sertifikasi_dosen = $employee->tanggal_sertifikasi_dosen;
        $this->id_status_aktif = $employee->id_status_aktif;
        $this->status_aktif = $employee->status_aktif;
        $this->id_status_kepegawaian = $employee->id_status_kepegawaian;
        $this->status_kepegawaian = $employee->status_kepegawaian;
        $this->id_pangkat = $employee->id_pangkat;
        $this->id_jabatan_fungsional = $employee->id_jabatan_fungsional;
        $this->jabatan_fungsional = $employee->jabatan_fungsional;
        $this->id_jabatan_sub_fungsional = $employee->id_jabatan_sub_fungsional;
        $this->jabatan_sub_fungsional = $employee->jabatan_sub_fungsional;
        $this->id_jabatan_struktural = $employee->id_jabatan_struktural;
        $this->jabatan_struktural = $employee->jabatan_struktural;
        $this->is_deleted = $employee->is_deleted;
        $this->id_sso = $employee->id_sso;
        $this->last_sync_at = $employee->last_sync_at;

        // Load fingerprint_pin from User
        $user = \App\Models\User::where('employee_id', $employee->id)
            ->where('employee_type', 'employee')
            ->first();
        $this->fingerprint_pin = $user ? $user->fingerprint_pin : '';

        $this->showEditModal = true;
    }

    public function save()
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'nip' => 'nullable|string|max:255',
            'fingerprint_pin' => 'nullable|string|max:255',
            'jenis_kelamin' => 'nullable|string|max:10',
            'satuan_kerja' => 'nullable|string|max:255',
            'status_aktif' => 'nullable|string|max:255',
            'jabatan_struktural' => 'nullable|string|max:255',
        ];

        $validatedData = $this->validate($rules);

        try {
            DB::beginTransaction();

            $employeeData = [
                'nama' => $this->nama,
                'email' => $this->email,
                'nip' => $this->nip,
                'jenis_kelamin' => $this->jenis_kelamin,
                'satuan_kerja' => $this->satuan_kerja,
                'status_aktif' => $this->status_aktif ?: 'Aktif',
                'jabatan_struktural' => $this->jabatan_struktural,
                'last_sync_at' => now(), // Mark as manual/updated
            ];

            if ($this->employee_id) {
                // Update existing employee
                $employee = Employee::findOrFail($this->employee_id);
                $employee->update($employeeData);

                // Update associated user
                $user = \App\Models\User::where('employee_id', $employee->id)
                    ->where('employee_type', 'employee')
                    ->first();

                if ($user) {
                    $user->update([
                        'name' => $this->nama,
                        'email' => $this->email,
                        'fingerprint_pin' => $this->fingerprint_pin,
                        'fingerprint_enabled' => $this->fingerprint_pin ? true : false,
                    ]);
                }

                session()->flash('success', 'Data karyawan berhasil diperbarui.');
            } else {
                // Create new employee
                $employee = Employee::create($employeeData);

                // Create associated user
                $user = \App\Models\User::where('email', $this->email)->first();
                if (! $user) {
                    $user = \App\Models\User::create([
                        'name' => $this->nama,
                        'email' => $this->email,
                        'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                        'employee_id' => $employee->id,
                        'employee_type' => 'employee',
                        'fingerprint_pin' => $this->fingerprint_pin,
                        'fingerprint_enabled' => $this->fingerprint_pin ? true : false,
                        'email_verified_at' => now(),
                    ]);

                    $staffRole = \Spatie\Permission\Models\Role::where('name', 'staff')->first();
                    if ($staffRole) {
                        $user->assignRole($staffRole);
                    }
                } else {
                    // Link existing user
                    $user->update([
                        'employee_id' => $employee->id,
                        'employee_type' => 'employee',
                        'fingerprint_pin' => $this->fingerprint_pin,
                        'fingerprint_enabled' => $this->fingerprint_pin ? true : false,
                    ]);
                }

                session()->flash('success', 'Data karyawan manual berhasil dibuat.');
            }

            DB::commit();
            $this->closeEditModal();
            $this->dispatch('refreshEmployees');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving manual employee: '.$e->getMessage());
            session()->flash('error', 'Gagal menyimpan: '.$e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $employee = Employee::findOrFail($id);
            $employee->delete();

            session()->flash('success', 'Data karyawan berhasil dihapus.');
            $this->dispatch('refreshEmployees');
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus data karyawan: '.$e->getMessage());
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->resetInputFields();
    }

    public function resetInputFields()
    {
        $this->employee_id = null;
        $this->id_pegawai = '';
        $this->nik = '';
        $this->nip = '';
        $this->nip_pns = '';
        $this->nidn = '';
        $this->nup = '';
        $this->nidk = '';
        $this->nupn = '';
        $this->nama = '';
        $this->gelar_depan = '';
        $this->gelar_belakang = '';
        $this->jenis_kelamin = '';
        $this->id_agama = '';
        $this->agama = '';
        $this->id_kewarganegaraan = '';
        $this->kewarganegaraan = '';
        $this->tanggal_lahir = '';
        $this->tempat_lahir = '';
        $this->status_nikah = '';
        $this->alamat_ktp = '';
        $this->rt_ktp = '';
        $this->rw_ktp = '';
        $this->kelurahan_ktp = '';
        $this->kecamatan_ktp = '';
        $this->kota_ktp = '';
        $this->provinsi_ktp = '';
        $this->kode_pos_ktp = '';
        $this->alamat_domisili = '';
        $this->rt_domisili = '';
        $this->rw_domisili = '';
        $this->kelurahan_domisili = '';
        $this->kecamatan_domisili = '';
        $this->kota_domisili = '';
        $this->provinsi_domisili = '';
        $this->kode_pos_domisili = '';
        $this->telepon = '';
        $this->telepon_kantor = '';
        $this->telepon_alternatif = '';
        $this->hp = '';
        $this->email = '';
        $this->email_kampus = '';
        $this->id_satuan_kerja = '';
        $this->satuan_kerja = '';
        $this->id_home_base = '';
        $this->home_base = '';
        $this->id_pendidikan_terakhir = '';
        $this->tanggal_masuk = '';
        $this->tanggal_sertifikasi_dosen = '';
        $this->id_status_aktif = '';
        $this->status_aktif = '';
        $this->id_status_kepegawaian = '';
        $this->status_kepegawaian = '';
        $this->id_pangkat = '';
        $this->id_jabatan_fungsional = '';
        $this->jabatan_fungsional = '';
        $this->id_jabatan_sub_fungsional = '';
        $this->jabatan_sub_fungsional = '';
        $this->id_jabatan_struktural = '';
        $this->jabatan_struktural = '';
        $this->is_deleted = '';
        $this->id_sso = '';
        $this->last_sync_at = '';
        $this->fingerprint_pin = '';
        $this->resetValidation();
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewEmployee = null;
        $this->viewEmployeeId = null;
    }

    public function getUnitKerjaListProperty()
    {
        return Employee::whereNotNull('satuan_kerja')
            ->distinct()
            ->pluck('satuan_kerja')
            ->filter()
            ->sort()
            ->values();
    }

    // Pagination Methods
    public function previousPage()
    {
        $this->setPage(max(1, $this->page - 1));
    }

    public function nextPage()
    {
        $this->setPage(min($this->page + 1, $this->employees->lastPage()));
    }

    public function gotoPage($page)
    {
        $this->setPage($page);
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filterUnitKerja = '';
        $this->filterStatusAktif = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Employee::query();

        $latestSyncRun = null;

        if (Schema::hasTable('sync_runs') && Schema::hasTable('sync_run_items')) {
            $latestSyncRun = SyncRun::query()
                ->whereIn('mode', ['employee', 'all'])
                ->with(['items' => fn ($q) => $q->latest('id')->limit(20)])
                ->latest('id')
                ->first();
        }

        $latestSyncRunItems = $latestSyncRun ? $latestSyncRun->items : collect();

        if ($this->search) {
            // Update search scope to work with new field names
            $query->where(function ($q) {
                $q->where('nama', 'like', '%'.$this->search.'%')
                    ->orWhere('nip', 'like', '%'.$this->search.'%')
                    ->orWhere('nik', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('satuan_kerja', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterUnitKerja) {
            $query->where('satuan_kerja', $this->filterUnitKerja);
        }

        if ($this->filterStatusAktif) {
            $query->where('status_aktif', $this->filterStatusAktif);
        }

        $employees = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.sdm.employee-management', [
            'employees' => $employees,
            'unitKerjaList' => $this->unitKerjaList,
            'latestSyncRun' => $latestSyncRun,
            'latestSyncRunItems' => $latestSyncRunItems,
        ]);
    }
}
