<?php

namespace Tests\Feature\Sync;

use App\Livewire\Sdm\DosenManagement;
use App\Livewire\Sdm\EmployeeManagement;
use App\Models\SyncRun;
use App\Models\SyncRunItem;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class SyncRunOperatorPanelTest extends TestCase
{
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
            '--path' => $migrationBasePath.'/2025_09_26_170000_create_employees_table_from_sevima.php',
            '--realpath' => true,
        ]);

        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2025_09_13_223620_create_dosens_table.php',
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
    }

    public function test_employee_management_exposes_latest_employee_sync_run_and_items(): void
    {
        $employeeRun = SyncRun::create([
            'mode' => 'employee',
            'status' => 'completed',
            'idempotency_key' => 'employee-run',
            'fetched_count' => 10,
            'processed_count' => 9,
            'failed_count' => 1,
            'error_summary' => [
                'error_count' => 1,
                'reconcile' => ['linked_count' => 2, 'conflict_count' => 1],
            ],
        ]);

        SyncRunItem::create([
            'sync_run_id' => $employeeRun->id,
            'entity_type' => 'employee',
            'level' => 'warning',
            'message' => 'User link reconciliation conflict',
            'payload' => ['nip' => 'EMP-1'],
        ]);

        Livewire::test(EmployeeManagement::class)
            ->assertViewHas('latestSyncRun', fn ($run) => $run !== null && $run->id === $employeeRun->id)
            ->assertViewHas('latestSyncRunItems', fn ($items) => $items->count() === 1);
    }

    public function test_dosen_management_exposes_latest_dosen_sync_run_and_items(): void
    {
        $dosenRun = SyncRun::create([
            'mode' => 'dosen',
            'status' => 'completed',
            'idempotency_key' => 'dosen-run',
            'fetched_count' => 8,
            'processed_count' => 8,
            'failed_count' => 0,
            'error_summary' => [
                'error_count' => 0,
                'reconcile' => ['linked_count' => 3, 'conflict_count' => 0],
            ],
        ]);

        SyncRunItem::create([
            'sync_run_id' => $dosenRun->id,
            'entity_type' => 'dosen',
            'level' => 'warning',
            'message' => 'User link reconciliation conflict',
            'payload' => ['nip' => 'DSN-1'],
        ]);

        Livewire::test(DosenManagement::class)
            ->assertViewHas('latestSyncRun', fn ($run) => $run !== null && $run->id === $dosenRun->id)
            ->assertViewHas('latestSyncRunItems', fn ($items) => $items->count() === 1);
    }
}
