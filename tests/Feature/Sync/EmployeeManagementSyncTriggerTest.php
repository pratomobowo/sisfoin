<?php

namespace Tests\Feature\Sync;

use App\Jobs\Sync\RunSdmSyncJob;
use App\Livewire\Sdm\EmployeeManagement;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use Tests\TestCase;

class EmployeeManagementSyncTriggerTest extends TestCase
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
            '--path' => $migrationBasePath.'/2026_02_23_000001_create_sync_runs_table.php',
            '--realpath' => true,
        ]);

        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2026_02_23_000002_create_sync_run_items_table.php',
            '--realpath' => true,
        ]);
    }

    public function test_sync_button_schedules_async_sync_run(): void
    {
        Bus::fake();

        Livewire::test(EmployeeManagement::class)
            ->call('syncSevima');

        $this->assertDatabaseHas('sync_runs', [
            'mode' => 'all',
            'status' => 'pending',
        ]);

        Bus::assertDispatched(RunSdmSyncJob::class);
    }
}
