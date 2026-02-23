<?php

namespace Tests\Feature\Livewire\Sdm;

use App\Livewire\Sdm\WorkShiftManager;
use App\Models\User;
use App\Models\WorkShift;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\Feature\Sync\Concerns\CreatesActivityLogTable;
use Tests\TestCase;

class WorkShiftManagerHardeningTest extends TestCase
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
            '--path' => $migrationBasePath.'/2026_01_19_185853_create_work_shifts_table.php',
            '--realpath' => true,
        ]);

        $this->ensureActivityLogTableExists();
    }

    public function test_set_as_default_invalid_id_keeps_existing_default(): void
    {
        $actor = User::create([
            'name' => 'Shift Admin',
            'email' => 'shift-admin@example.com',
            'password' => 'password',
            'nip' => 'SHIFT-ADM-1',
        ]);

        $currentDefault = WorkShift::where('is_default', true)->firstOrFail();

        $this->actingAs($actor);

        Livewire::test(WorkShiftManager::class)
            ->call('setAsDefault', 999999);

        $this->assertDatabaseHas('work_shifts', [
            'id' => $currentDefault->id,
            'is_default' => true,
        ]);
    }

    public function test_set_as_default_rejects_inactive_shift(): void
    {
        $actor = User::create([
            'name' => 'Shift Admin 2',
            'email' => 'shift-admin2@example.com',
            'password' => 'password',
            'nip' => 'SHIFT-ADM-2',
        ]);

        $currentDefault = WorkShift::where('is_default', true)->firstOrFail();

        $inactiveShift = WorkShift::create([
            'name' => 'Shift Inactive',
            'code' => 'INACTIVE',
            'start_time' => '09:00:00',
            'end_time' => '15:00:00',
            'early_arrival_threshold' => '08:40:00',
            'late_tolerance_minutes' => 5,
            'work_hours' => 6,
            'color' => 'gray',
            'is_default' => false,
            'is_active' => false,
        ]);

        $this->actingAs($actor);

        Livewire::test(WorkShiftManager::class)
            ->call('setAsDefault', $inactiveShift->id);

        $this->assertDatabaseHas('work_shifts', [
            'id' => $currentDefault->id,
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('work_shifts', [
            'id' => $inactiveShift->id,
            'is_default' => false,
        ]);
    }
}
