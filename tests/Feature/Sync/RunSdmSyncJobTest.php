<?php

namespace Tests\Feature\Sync;

use App\Jobs\Sync\RunSdmSyncJob;
use App\Models\SyncRun;
use App\Models\User;
use App\Services\SevimaApiService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Tests\Feature\Sync\Concerns\CreatesActivityLogTable;
use Tests\TestCase;

class RunSdmSyncJobTest extends TestCase
{
    use CreatesActivityLogTable;

    protected function setUp(): void
    {
        parent::setUp();

        $migrationBasePath = is_file(getcwd().'/database/migrations/0001_01_01_000000_create_users_table.php')
            ? getcwd().'/database/migrations'
            : base_path('database/migrations');

        Artisan::call('migrate:fresh', [
            '--path' => $migrationBasePath.'/0001_01_01_000000_create_users_table.php',
            '--realpath' => true,
        ]);

        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2025_09_13_230642_add_nip_to_users_table.php',
            '--realpath' => true,
        ]);

        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2025_09_13_223620_create_dosens_table.php',
            '--realpath' => true,
        ]);

        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2025_09_26_170000_create_employees_table_from_sevima.php',
            '--realpath' => true,
        ]);

        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2026_02_23_000001_create_sync_runs_table.php',
            '--realpath' => true,
        ]);

        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2026_02_23_000002_create_sync_run_items_table.php',
            '--realpath' => true,
        ]);

        $this->ensureActivityLogTableExists();
    }

    public function test_job_processes_dosen_mode_and_persists_counters_and_errors(): void
    {
        $fakeSevima = new class extends SevimaApiService
        {
            public function getDosen()
            {
                return [
                    ['id_pegawai' => 'D001', 'nama' => 'Dosen Satu', 'nip' => '111', 'status_aktif' => 'AA'],
                    ['id_pegawai' => 'D002', 'nama' => 'Dosen Dua', 'nip' => '222', 'status_aktif' => 'AA'],
                    ['nama' => 'Tanpa ID', 'nip' => '333'],
                ];
            }

            public function mapDosenToDosen(array $dosenData)
            {
                return [
                    'id_pegawai' => $dosenData['id_pegawai'] ?? null,
                    'nama' => $dosenData['nama'] ?? null,
                    'nip' => $dosenData['nip'] ?? null,
                    'status_aktif' => $dosenData['status_aktif'] ?? null,
                    'last_synced_at' => now(),
                ];
            }
        };

        app()->instance(SevimaApiService::class, $fakeSevima);

        $run = SyncRun::create([
            'mode' => 'dosen',
            'status' => 'pending',
            'idempotency_key' => 'run-dosen-test',
        ]);

        $job = new RunSdmSyncJob($run->id);
        $job->handle();

        $this->assertDatabaseHas('sync_runs', [
            'id' => $run->id,
            'status' => 'completed_with_warning',
            'fetched_count' => 3,
            'processed_count' => 2,
            'inserted_count' => 2,
            'updated_count' => 0,
            'failed_count' => 1,
        ]);

        $this->assertDatabaseCount('dosens', 2);
        $this->assertDatabaseCount('sync_run_items', 1);
        $this->assertDatabaseHas('sync_run_items', [
            'sync_run_id' => $run->id,
            'entity_type' => 'dosen',
            'level' => 'error',
        ]);
    }

    public function test_job_processes_employee_mode_and_persists_counters_and_errors(): void
    {
        Log::spy();

        $fakeSevima = new class extends SevimaApiService
        {
            public function getPegawai()
            {
                return [
                    ['id_pegawai' => 'E001', 'nama' => 'Pegawai Satu', 'nip' => '111', 'status_aktif' => 'Aktif'],
                    ['id_pegawai' => 'E002', 'nama' => 'Pegawai Dua', 'nip' => '222', 'status_aktif' => 'Aktif'],
                    ['nama' => 'Tanpa ID', 'nip' => '333'],
                ];
            }

            public function mapPegawaiToEmployee(array $pegawaiData)
            {
                return [
                    'id_pegawai' => $pegawaiData['id_pegawai'] ?? null,
                    'nama' => $pegawaiData['nama'] ?? null,
                    'nip' => $pegawaiData['nip'] ?? null,
                    'status_aktif' => $pegawaiData['status_aktif'] ?? null,
                    'last_sync_at' => now(),
                ];
            }
        };

        app()->instance(SevimaApiService::class, $fakeSevima);

        $run = SyncRun::create([
            'mode' => 'employee',
            'status' => 'pending',
            'idempotency_key' => 'run-employee-test',
        ]);

        $job = new RunSdmSyncJob($run->id);
        $job->handle();

        $this->assertDatabaseHas('sync_runs', [
            'id' => $run->id,
            'status' => 'completed_with_warning',
            'fetched_count' => 3,
            'processed_count' => 2,
            'inserted_count' => 2,
            'updated_count' => 0,
            'failed_count' => 1,
        ]);

        $this->assertDatabaseCount('employees', 2);
        $this->assertDatabaseCount('sync_run_items', 1);
        $this->assertDatabaseHas('sync_run_items', [
            'sync_run_id' => $run->id,
            'entity_type' => 'employee',
            'level' => 'error',
        ]);

        Log::shouldHaveReceived('info')
            ->with('Employee sync fetched records', [
                'sync_run_id' => $run->id,
                'fetched_count' => 3,
            ])->once();

        Log::shouldHaveReceived('info')
            ->with('Employee sync write summary', \Mockery::on(function (array $context): bool {
                return ($context['sync_run_id'] ?? null) !== null
                    && ($context['processed_count'] ?? null) === 2
                    && ($context['inserted_count'] ?? null) === 2
                    && ($context['updated_count'] ?? null) === 0
                    && ($context['failed_count'] ?? null) === 1;
            }))
            ->once();
    }

    public function test_job_processes_all_mode_and_aggregates_employee_and_dosen_results(): void
    {
        $fakeSevima = new class extends SevimaApiService
        {
            public function getPegawai()
            {
                return [
                    ['id_pegawai' => 'E001', 'nama' => 'Pegawai Satu', 'nip' => '111', 'status_aktif' => 'Aktif'],
                    ['nama' => 'Pegawai Tanpa ID', 'nip' => '999'],
                ];
            }

            public function getDosen()
            {
                return [
                    ['id_pegawai' => 'D001', 'nama' => 'Dosen Satu', 'nip' => '211', 'status_aktif' => 'AA'],
                    ['nama' => 'Dosen Tanpa ID', 'nip' => '299'],
                ];
            }

            public function mapPegawaiToEmployee(array $pegawaiData)
            {
                return [
                    'id_pegawai' => $pegawaiData['id_pegawai'] ?? null,
                    'nama' => $pegawaiData['nama'] ?? null,
                    'nip' => $pegawaiData['nip'] ?? null,
                    'status_aktif' => $pegawaiData['status_aktif'] ?? null,
                    'last_sync_at' => now(),
                ];
            }

            public function mapDosenToDosen(array $dosenData)
            {
                return [
                    'id_pegawai' => $dosenData['id_pegawai'] ?? null,
                    'nama' => $dosenData['nama'] ?? null,
                    'nip' => $dosenData['nip'] ?? null,
                    'status_aktif' => $dosenData['status_aktif'] ?? null,
                    'last_synced_at' => now(),
                ];
            }
        };

        app()->instance(SevimaApiService::class, $fakeSevima);

        $run = SyncRun::create([
            'mode' => 'all',
            'status' => 'pending',
            'idempotency_key' => 'run-all-test',
        ]);

        $job = new RunSdmSyncJob($run->id);
        $job->handle();

        $this->assertDatabaseHas('sync_runs', [
            'id' => $run->id,
            'status' => 'completed_with_warning',
            'fetched_count' => 4,
            'processed_count' => 2,
            'inserted_count' => 2,
            'updated_count' => 0,
            'failed_count' => 2,
        ]);

        $this->assertDatabaseCount('employees', 1);
        $this->assertDatabaseCount('dosens', 1);
        $this->assertDatabaseCount('sync_run_items', 2);
        $this->assertDatabaseHas('sync_run_items', [
            'sync_run_id' => $run->id,
            'entity_type' => 'employee',
            'level' => 'error',
        ]);
        $this->assertDatabaseHas('sync_run_items', [
            'sync_run_id' => $run->id,
            'entity_type' => 'dosen',
            'level' => 'error',
        ]);
    }

    public function test_job_reconciles_user_links_after_all_mode_sync(): void
    {
        $employeeUser = User::create([
            'name' => 'Emp User',
            'email' => 'emp-link@example.com',
            'password' => 'password',
            'nip' => 'E-NIP-1',
            'employee_type' => 'employee',
            'employee_id' => null,
        ]);

        $dosenUser = User::create([
            'name' => 'Dosen User',
            'email' => 'dosen-link@example.com',
            'password' => 'password',
            'nip' => 'D-NIP-1',
            'employee_type' => 'dosen',
            'employee_id' => null,
        ]);

        $fakeSevima = new class extends SevimaApiService
        {
            public function getPegawai()
            {
                return [
                    ['id_pegawai' => 'E101', 'nama' => 'Pegawai Link', 'nip' => 'E-NIP-1', 'status_aktif' => 'Aktif'],
                ];
            }

            public function getDosen()
            {
                return [
                    ['id_pegawai' => 'D101', 'nama' => 'Dosen Link', 'nip' => 'D-NIP-1', 'status_aktif' => 'AA'],
                ];
            }

            public function mapPegawaiToEmployee(array $pegawaiData)
            {
                return [
                    'id_pegawai' => $pegawaiData['id_pegawai'] ?? null,
                    'nama' => $pegawaiData['nama'] ?? null,
                    'nip' => $pegawaiData['nip'] ?? null,
                    'status_aktif' => $pegawaiData['status_aktif'] ?? null,
                    'last_sync_at' => now(),
                ];
            }

            public function mapDosenToDosen(array $dosenData)
            {
                return [
                    'id_pegawai' => $dosenData['id_pegawai'] ?? null,
                    'nama' => $dosenData['nama'] ?? null,
                    'nip' => $dosenData['nip'] ?? null,
                    'status_aktif' => $dosenData['status_aktif'] ?? null,
                    'last_synced_at' => now(),
                ];
            }
        };

        app()->instance(SevimaApiService::class, $fakeSevima);

        $run = SyncRun::create([
            'mode' => 'all',
            'status' => 'pending',
            'idempotency_key' => 'run-all-reconcile-test',
        ]);

        (new RunSdmSyncJob($run->id))->handle();

        $this->assertDatabaseHas('users', [
            'id' => $employeeUser->id,
            'employee_type' => 'employee',
            'employee_id' => '1',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $dosenUser->id,
            'employee_type' => 'dosen',
            'employee_id' => '1',
        ]);

        $run->refresh();
        $this->assertSame(2, $run->error_summary['reconcile']['linked_count'] ?? null);
        $this->assertSame(0, $run->error_summary['reconcile']['conflict_count'] ?? null);
    }

    public function test_job_persists_reconcile_conflict_summary_and_warning_items(): void
    {
        User::create([
            'name' => 'Conflict Emp User',
            'email' => 'emp-conflict@example.com',
            'password' => 'password',
            'nip' => 'EMP-CONFLICT',
            'employee_type' => 'employee',
            'employee_id' => null,
        ]);

        $fakeSevima = new class extends SevimaApiService
        {
            public function getPegawai()
            {
                return [
                    ['id_pegawai' => 'E201', 'nama' => 'Pegawai A', 'nip' => 'EMP-CONFLICT', 'status_aktif' => 'Aktif'],
                    ['id_pegawai' => 'E202', 'nama' => 'Pegawai B', 'nip' => 'EMP-CONFLICT', 'status_aktif' => 'Aktif'],
                ];
            }

            public function mapPegawaiToEmployee(array $pegawaiData)
            {
                return [
                    'id_pegawai' => $pegawaiData['id_pegawai'] ?? null,
                    'nama' => $pegawaiData['nama'] ?? null,
                    'nip' => $pegawaiData['nip'] ?? null,
                    'status_aktif' => $pegawaiData['status_aktif'] ?? null,
                    'last_sync_at' => now(),
                ];
            }
        };

        app()->instance(SevimaApiService::class, $fakeSevima);

        $run = SyncRun::create([
            'mode' => 'employee',
            'status' => 'pending',
            'idempotency_key' => 'run-employee-conflict-summary-test',
        ]);

        (new RunSdmSyncJob($run->id))->handle();

        $run->refresh();

        $this->assertSame('completed_with_warning', $run->status);
        $this->assertSame(0, $run->error_summary['reconcile']['linked_count'] ?? null);
        $this->assertSame(1, $run->error_summary['reconcile']['conflict_count'] ?? null);
        $this->assertSame(0, $run->error_summary['error_count'] ?? null);
        $this->assertDatabaseHas('sync_run_items', [
            'sync_run_id' => $run->id,
            'entity_type' => 'employee',
            'level' => 'warning',
            'message' => 'User link reconciliation conflict',
        ]);
    }

    public function test_job_marks_completed_with_warning_when_sync_errors_exist_without_reconcile_conflict(): void
    {
        $fakeSevima = new class extends SevimaApiService
        {
            public function getPegawai()
            {
                return [
                    ['id_pegawai' => 'E301', 'nama' => 'Pegawai Valid', 'nip' => 'E-VALID-1', 'status_aktif' => 'Aktif'],
                    ['nama' => 'Pegawai Missing ID', 'nip' => 'E-INVALID-1'],
                ];
            }

            public function mapPegawaiToEmployee(array $pegawaiData)
            {
                return [
                    'id_pegawai' => $pegawaiData['id_pegawai'] ?? null,
                    'nama' => $pegawaiData['nama'] ?? null,
                    'nip' => $pegawaiData['nip'] ?? null,
                    'status_aktif' => $pegawaiData['status_aktif'] ?? null,
                    'last_sync_at' => now(),
                ];
            }
        };

        app()->instance(SevimaApiService::class, $fakeSevima);

        $run = SyncRun::create([
            'mode' => 'employee',
            'status' => 'pending',
            'idempotency_key' => 'run-employee-warning-on-error-test',
        ]);

        (new RunSdmSyncJob($run->id))->handle();

        $run->refresh();

        $this->assertSame('completed_with_warning', $run->status);
        $this->assertSame(1, $run->error_summary['error_count'] ?? null);
        $this->assertSame(0, $run->error_summary['reconcile']['conflict_count'] ?? null);
    }

    public function test_all_mode_continues_when_dosen_api_is_blocked_and_marks_warning(): void
    {
        $fakeSevima = new class extends SevimaApiService
        {
            public function getPegawai()
            {
                return [
                    ['id_pegawai' => 'E401', 'nama' => 'Pegawai Satu', 'nip' => 'E-401', 'status_aktif' => 'Aktif'],
                ];
            }

            public function getDosen()
            {
                throw new \Exception('Error fetching dosen data: Failed to fetch dosen data: 403');
            }

            public function mapPegawaiToEmployee(array $pegawaiData)
            {
                return [
                    'id_pegawai' => $pegawaiData['id_pegawai'] ?? null,
                    'nama' => $pegawaiData['nama'] ?? null,
                    'nip' => $pegawaiData['nip'] ?? null,
                    'status_aktif' => $pegawaiData['status_aktif'] ?? null,
                    'last_sync_at' => now(),
                ];
            }
        };

        app()->instance(SevimaApiService::class, $fakeSevima);

        $run = SyncRun::create([
            'mode' => 'all',
            'status' => 'pending',
            'idempotency_key' => 'run-all-continue-on-dosen-403',
        ]);

        (new RunSdmSyncJob($run->id))->handle();

        $run->refresh();

        $this->assertSame('completed_with_warning', $run->status);
        $this->assertSame(1, $run->processed_count);
        $this->assertGreaterThanOrEqual(1, $run->error_summary['error_count'] ?? 0);
        $this->assertDatabaseHas('sync_run_items', [
            'sync_run_id' => $run->id,
            'entity_type' => 'dosen',
            'level' => 'error',
        ]);
    }
}
