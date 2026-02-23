<?php

namespace Tests\Feature\Sync;

use App\Livewire\Sdm\DosenManagement;
use App\Livewire\Sdm\EmployeeManagement;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class SyncViewGracefulWithoutTablesTest extends TestCase
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

        // Intentionally do not migrate sync_runs/sync_run_items
    }

    public function test_employee_management_renders_without_sync_tables(): void
    {
        Livewire::test(EmployeeManagement::class)
            ->assertStatus(200);
    }

    public function test_dosen_management_renders_without_sync_tables(): void
    {
        Livewire::test(DosenManagement::class)
            ->assertStatus(200);
    }
}
