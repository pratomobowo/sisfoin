<?php

namespace Tests\Feature\Livewire\Sdm;

use App\Livewire\Sdm\AttendanceMonitor;
use App\Models\AttendanceSetting;
use App\Models\Employee;
use App\Models\Employee\Attendance as EmployeeAttendance;
use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AttendanceMonitorTest extends TestCase
{
    use RefreshDatabase;

    private function seedWorkingDaysSetting(): void
    {
        AttendanceSetting::updateOrCreate(
            ['key' => 'working_days'],
            [
                'value' => '1,2,3,4,5',
                'type' => 'array',
                'label' => 'Working Days',
                'description' => 'Hari kerja',
                'group' => 'attendance',
                'sort_order' => 1,
            ]
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_sdm_can_access_attendance_monitor_route(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin-sdm');

        $this->actingAs($user);
        setActiveRole('admin-sdm');

        $this->get(route('sdm.absensi.monitor'))
            ->assertOk();
    }

    public function test_daily_mode_marks_absent_for_working_day_without_attendance_record(): void
    {
        $this->seedWorkingDaysSetting();

        $monday = Carbon::parse('2026-02-16');

        Employee::create([
            'nip' => '198800001',
            'nama' => 'Budi Test',
            'status_aktif' => 'Aktif',
            'satuan_kerja' => 'IT',
        ]);

        $sdm = User::factory()->create();
        $sdm->assignRole('admin-sdm');

        $this->actingAs($sdm);
        setActiveRole('admin-sdm');

        Livewire::test(AttendanceMonitor::class)
            ->set('mode', 'daily')
            ->set('selectedDate', $monday->format('Y-m-d'))
            ->assertSee('Tidak Hadir')
            ->assertSee('Budi Test');
    }

    public function test_daily_mode_does_not_mark_absent_on_holiday_without_attendance_record(): void
    {
        $this->seedWorkingDaysSetting();

        $monday = Carbon::parse('2026-02-16');

        Holiday::create([
            'date' => $monday->format('Y-m-d'),
            'name' => 'Hari Libur Test',
            'type' => 'company',
            'is_recurring' => false,
        ]);

        Employee::create([
            'nip' => '198800002',
            'nama' => 'Sinta Test',
            'status_aktif' => 'Aktif',
            'satuan_kerja' => 'HR',
        ]);

        $sdm = User::factory()->create();
        $sdm->assignRole('admin-sdm');

        $this->actingAs($sdm);
        setActiveRole('admin-sdm');

        Livewire::test(AttendanceMonitor::class)
            ->set('mode', 'daily')
            ->set('selectedDate', $monday->format('Y-m-d'))
            ->assertSee('Libur');
    }

    public function test_range_mode_builds_employee_summary(): void
    {
        $this->seedWorkingDaysSetting();

        $employee = Employee::create([
            'nip' => '198800003',
            'nama' => 'Rina Summary',
            'status_aktif' => 'Aktif',
            'satuan_kerja' => 'Finance',
        ]);

        $user = User::factory()->create([
            'nip' => '198800003',
            'employee_type' => 'staff',
        ]);

        EmployeeAttendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-16',
            'status' => 'on_time',
        ]);

        EmployeeAttendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-17',
            'status' => 'late',
        ]);

        $sdm = User::factory()->create();
        $sdm->assignRole('admin-sdm');

        $this->actingAs($sdm);
        setActiveRole('admin-sdm');

        Livewire::test(AttendanceMonitor::class)
            ->set('mode', 'range')
            ->set('dateFrom', '2026-02-16')
            ->set('dateTo', '2026-02-19')
            ->assertSee('Rina Summary')
            ->assertSee('Finance')
            ->assertSee('50.00%');

        $this->assertSame('Finance', $employee->satuan_kerja);
    }
}
