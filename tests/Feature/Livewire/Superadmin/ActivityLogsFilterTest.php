<?php

namespace Tests\Feature\Livewire\Superadmin;

use App\Livewire\Superadmin\ActivityLogs;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityLogsFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_component_exposes_filter_properties_for_standard_audit_dimensions(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        Livewire::test(ActivityLogs::class)
            ->assertSet('moduleFilter', '')
            ->assertSet('riskLevelFilter', '')
            ->assertSet('resultFilter', '');
    }

    public function test_reset_filters_clears_standard_audit_dimension_filters(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        Livewire::test(ActivityLogs::class)
            ->set('moduleFilter', 'attendance')
            ->set('riskLevelFilter', 'high')
            ->set('resultFilter', 'failed')
            ->call('resetFilters')
            ->assertSet('moduleFilter', '')
            ->assertSet('riskLevelFilter', '')
            ->assertSet('resultFilter', '');
    }

    public function test_activity_model_supports_json_metadata_filters_for_module_risk_and_result(): void
    {
        ActivityLog::query()->create([
            'log_name' => 'attendance_operations',
            'description' => 'Reprocess all attendance logs',
            'event' => 'execute',
            'action' => 'attendance.logs.reprocess_all',
            'metadata' => [
                'module' => 'attendance',
                'risk_level' => 'high',
                'result' => 'success',
            ],
        ]);

        ActivityLog::query()->create([
            'log_name' => 'auth',
            'description' => 'User logged in',
            'event' => 'login',
            'action' => 'auth.session.login',
            'metadata' => [
                'module' => 'auth',
                'risk_level' => 'low',
                'result' => 'success',
            ],
        ]);

        $records = ActivityLog::query()
            ->where('metadata->module', 'attendance')
            ->where('metadata->risk_level', 'high')
            ->where('metadata->result', 'success')
            ->get();

        $this->assertCount(1, $records);
        $this->assertSame('attendance.logs.reprocess_all', $records->first()->action);
    }
}
