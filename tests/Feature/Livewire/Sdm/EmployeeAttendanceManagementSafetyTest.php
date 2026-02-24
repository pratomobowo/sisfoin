<?php

namespace Tests\Feature\Livewire\Sdm;

use App\Livewire\Sdm\EmployeeAttendanceManagement;
use App\Models\ActivityLog;
use App\Models\Employee\Attendance as EmployeeAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EmployeeAttendanceManagementSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_clear_all_attendance_requires_risk_acknowledgement(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin-sdm');

        $this->actingAs($admin);
        setActiveRole('admin-sdm');

        EmployeeAttendance::create([
            'user_id' => $admin->id,
            'date' => now()->toDateString(),
            'status' => 'on_time',
        ]);

        Livewire::test(EmployeeAttendanceManagement::class)
            ->set('clearConfirmation', 'HAPUS ABSENSI')
            ->set('clearDangerAcknowledged', false)
            ->call('clearAllEmployeeAttendance')
            ->assertDispatched('toast-show', function (string $name, array $params): bool {
                return $name === 'toast-show'
                    && ($params['type'] ?? null) === 'warning';
            });

        $this->assertSame(1, EmployeeAttendance::count());

        $entry = ActivityLog::query()->latest('id')->first();

        $this->assertNotNull($entry);
        $this->assertSame('attendance_operations', $entry->log_name);
        $this->assertSame('delete', $entry->event);
        $this->assertSame('attendance.records.clear_all', $entry->action);
        $this->assertSame('blocked', $entry->metadata['result'] ?? null);
        $this->assertSame('critical', $entry->metadata['risk_level'] ?? null);
    }
}
