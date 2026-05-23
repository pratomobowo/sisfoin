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

    public function test_daily_mode_shows_attendance_for_employee_with_non_standard_employee_type(): void
    {
        $this->seedWorkingDaysSetting();

        $monday = Carbon::parse('2026-02-16');

        $employee = Employee::create([
            'nip' => '198800004',
            'nama' => 'Andi Staff',
            'status_aktif' => 'Aktif',
            'satuan_kerja' => 'IT',
        ]);

        $user = User::factory()->create([
            'nip' => '198800004',
            'employee_type' => 'staff', // Non-standard employee_type
            'employee_id' => $employee->id,
        ]);

        EmployeeAttendance::create([
            'user_id' => $user->id,
            'date' => $monday->format('Y-m-d'),
            'status' => 'on_time',
        ]);

        $sdm = User::factory()->create();
        $sdm->assignRole('admin-sdm');

        $this->actingAs($sdm);
        setActiveRole('admin-sdm');

        Livewire::test(AttendanceMonitor::class)
            ->set('mode', 'daily')
            ->set('selectedDate', $monday->format('Y-m-d'))
            ->assertSee('Andi Staff')
            ->assertSee('Hadir')
            ->assertViewHas('rows', function ($rows) {
                return collect($rows->items())->where('status', 'absent')->isEmpty();
            });
    }

    public function test_daily_mode_shows_attendance_mapped_by_nip_only(): void
    {
        $this->seedWorkingDaysSetting();

        $monday = Carbon::parse('2026-02-16');

        Employee::create([
            'nip' => '198800005',
            'nama' => 'Budi NIP Only',
            'status_aktif' => 'Aktif',
            'satuan_kerja' => 'HR',
        ]);

        $user = User::factory()->create([
            'nip' => '198800005',
            'employee_type' => null, // No employee_type
            'employee_id' => null,   // No employee_id
        ]);

        EmployeeAttendance::create([
            'user_id' => $user->id,
            'date' => $monday->format('Y-m-d'),
            'status' => 'late',
        ]);

        $sdm = User::factory()->create();
        $sdm->assignRole('admin-sdm');

        $this->actingAs($sdm);
        setActiveRole('admin-sdm');

        Livewire::test(AttendanceMonitor::class)
            ->set('mode', 'daily')
            ->set('selectedDate', $monday->format('Y-m-d'))
            ->assertSee('Budi NIP Only')
            ->assertSee('Terlambat')
            ->assertViewHas('rows', function ($rows) {
                return collect($rows->items())->where('status', 'absent')->isEmpty();
            });
    }

    public function test_daily_mode_status_filter_on_time_includes_on_time_present_and_early_arrival(): void
    {
        $this->seedWorkingDaysSetting();

        $monday = Carbon::parse('2026-02-16');

        $emp1 = Employee::create([
            'nip' => '198800011',
            'nama' => 'User OnTime',
            'status_aktif' => 'Aktif',
            'satuan_kerja' => 'HR',
        ]);
        $u1 = User::factory()->create([
            'nip' => '198800011',
            'employee_id' => $emp1->id,
        ]);
        EmployeeAttendance::create([
            'user_id' => $u1->id,
            'date' => $monday->format('Y-m-d'),
            'status' => 'on_time',
        ]);

        $emp2 = Employee::create([
            'nip' => '198800012',
            'nama' => 'User Present',
            'status_aktif' => 'Aktif',
            'satuan_kerja' => 'HR',
        ]);
        $u2 = User::factory()->create([
            'nip' => '198800012',
            'employee_id' => $emp2->id,
        ]);
        EmployeeAttendance::create([
            'user_id' => $u2->id,
            'date' => $monday->format('Y-m-d'),
            'status' => 'present',
        ]);

        $emp3 = Employee::create([
            'nip' => '198800013',
            'nama' => 'User Early',
            'status_aktif' => 'Aktif',
            'satuan_kerja' => 'HR',
        ]);
        $u3 = User::factory()->create([
            'nip' => '198800013',
            'employee_id' => $emp3->id,
        ]);
        EmployeeAttendance::create([
            'user_id' => $u3->id,
            'date' => $monday->format('Y-m-d'),
            'status' => 'early_arrival',
        ]);

        $emp4 = Employee::create([
            'nip' => '198800014',
            'nama' => 'User Late',
            'status_aktif' => 'Aktif',
            'satuan_kerja' => 'HR',
        ]);
        $u4 = User::factory()->create([
            'nip' => '198800014',
            'employee_id' => $emp4->id,
        ]);
        EmployeeAttendance::create([
            'user_id' => $u4->id,
            'date' => $monday->format('Y-m-d'),
            'status' => 'late',
        ]);

        $sdm = User::factory()->create();
        $sdm->assignRole('admin-sdm');

        $this->actingAs($sdm);
        setActiveRole('admin-sdm');

        Livewire::test(AttendanceMonitor::class)
            ->set('mode', 'daily')
            ->set('selectedDate', $monday->format('Y-m-d'))
            ->set('statusFilter', 'on_time')
            ->assertSee('User OnTime')
            ->assertSee('User Present')
            ->assertSee('User Early')
            ->assertDontSee('User Late')
            ->assertViewHas('rows', function ($rows) {
                $items = collect($rows->items());
                return $items->count() === 3
                    && $items->where('status', 'on_time')->isNotEmpty()
                    && $items->where('status', 'present')->isNotEmpty()
                    && $items->where('status', 'early_arrival')->isNotEmpty()
                    && $items->where('status', 'late')->isEmpty();
            });
    }
}
