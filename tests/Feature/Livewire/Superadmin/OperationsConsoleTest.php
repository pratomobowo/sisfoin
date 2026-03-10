<?php

namespace Tests\Feature\Livewire\Superadmin;

use App\Livewire\Superadmin\OperationsConsole;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OperationsConsoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_superadmin_can_access_operations_console_route(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        $this->get(route('superadmin.operations-console.index'))
            ->assertOk();
    }

    public function test_non_superadmin_cannot_access_operations_console_route(): void
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $this->actingAs($staff);
        setActiveRole('staff');

        $this->get(route('superadmin.operations-console.index'))
            ->assertStatus(403);
    }

    public function test_operations_console_shows_command_groups(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        Livewire::test(OperationsConsole::class)
            ->assertSee('Absensi')
            ->assertSee('SDM Sync')
            ->assertSee('Sistem')
            ->assertSee('users:relink-employee-links')
            ->assertSee('attendance:process')
            ->assertSee('optimize:clear');
    }

    public function test_superadmin_dry_run_relink_command_is_marked_as_preview(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        Livewire::test(OperationsConsole::class)
            ->set('selectedCommand', 'users:relink-employee-links')
            ->set('commandOptions.relink_type', 'employee')
            ->set('commandOptions.relink_fill_nip', true)
            ->set('commandOptions.relink_dry_run', true)
            ->set('confirmationText', 'RUN')
            ->call('runCommand')
            ->assertSet('lastRun.status', 'preview');

        $entry = ActivityLog::query()->latest('id')->first();

        $this->assertNotNull($entry);
        $this->assertSame('admin_operations', $entry->log_name);
        $this->assertSame('execute', $entry->event);
        $this->assertSame('system.command.run', $entry->action);
        $this->assertSame('superadmin', $entry->metadata['module'] ?? null);
        $this->assertSame('high', $entry->metadata['risk_level'] ?? null);
        $this->assertSame('preview', $entry->metadata['result'] ?? null);
    }

    public function test_cleanup_dry_run_is_marked_as_preview_not_successful_merge(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        Livewire::test(OperationsConsole::class)
            ->set('selectedCommand', 'cleanup:duplicates')
            ->set('commandOptions.cleanup_model', 'all')
            ->set('commandOptions.cleanup_dry_run', true)
            ->set('confirmationText', 'RUN')
            ->call('runCommand')
            ->assertSet('lastRun.status', 'preview');
    }
}
