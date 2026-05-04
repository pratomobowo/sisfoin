<?php

namespace Tests\Feature\Livewire\Sdm;

use App\Livewire\Sdm\EmployeeAttendanceManagement;
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

    }

    public function test_manual_attendance_correction_recalculates_total_and_overtime_hours(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin-sdm');

        $user = User::factory()->create();

        $attendance = EmployeeAttendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-16',
            'check_in_time' => '2026-02-16 08:00:00',
            'check_out_time' => '2026-02-16 14:00:00',
            'total_hours' => 6,
            'overtime_hours' => 0,
            'status' => 'on_time',
        ]);

        $this->actingAs($admin);
        setActiveRole('admin-sdm');

        Livewire::test(EmployeeAttendanceManagement::class)
            ->call('edit', $attendance->id)
            ->set('check_out_time', '15:30')
            ->call('save');

        $attendance->refresh();

        $this->assertSame('7.50', (string) $attendance->total_hours);
        $this->assertSame('1.50', (string) $attendance->overtime_hours);
    }

    public function test_manual_overnight_attendance_correction_uses_next_day_checkout(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin-sdm');

        $user = User::factory()->create();

        $attendance = EmployeeAttendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-16',
            'check_in_time' => '2026-02-16 22:00:00',
            'check_out_time' => null,
            'total_hours' => 0,
            'overtime_hours' => 0,
            'status' => 'incomplete',
        ]);

        $this->actingAs($admin);
        setActiveRole('admin-sdm');

        Livewire::test(EmployeeAttendanceManagement::class)
            ->call('edit', $attendance->id)
            ->set('check_out_time', '06:30')
            ->call('save');

        $attendance->refresh();

        $this->assertSame('2026-02-17 06:30:00', $attendance->check_out_time->format('Y-m-d H:i:s'));
        $this->assertSame('8.50', (string) $attendance->total_hours);
    }
}
