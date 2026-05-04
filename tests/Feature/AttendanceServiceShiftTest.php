<?php

namespace Tests\Feature;

use App\Models\AttendanceLog;
use App\Models\Employee\Attendance as EmployeeAttendance;
use App\Models\EmployeeShiftAssignment;
use App\Models\MesinFinger;
use App\Models\User;
use App\Models\WorkShift;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceServiceShiftTest extends TestCase
{
    use RefreshDatabase;

    public function test_processes_overnight_shift_as_single_attendance_day(): void
    {
        $user = User::factory()->create();
        $machine = MesinFinger::factory()->active()->create();

        $nightShift = WorkShift::create([
            'name' => 'Shift Malam Test',
            'code' => 'MALAM-TEST',
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
            'early_arrival_threshold' => '21:40:00',
            'late_tolerance_minutes' => 5,
            'work_hours' => 8,
            'color' => 'purple',
            'is_default' => true,
            'is_active' => true,
        ]);

        EmployeeShiftAssignment::create([
            'user_id' => $user->id,
            'work_shift_id' => $nightShift->id,
            'start_date' => '2026-02-16',
        ]);

        AttendanceLog::factory()->mapped($user->id)->atDateTime('2026-02-16 22:00:00')->create([
            'mesin_finger_id' => $machine->id,
            'status' => 0,
            'verify' => 1,
        ]);
        AttendanceLog::factory()->mapped($user->id)->atDateTime('2026-02-17 06:30:00')->create([
            'mesin_finger_id' => $machine->id,
            'status' => 0,
            'verify' => 1,
        ]);

        (new AttendanceService())->processLogs('2026-02-16', '2026-02-17');

        $this->assertSame(1, EmployeeAttendance::count());

        $attendance = EmployeeAttendance::first();

        $this->assertSame('2026-02-16', $attendance->date->format('Y-m-d'));
        $this->assertSame('2026-02-16 22:00:00', $attendance->check_in_time->format('Y-m-d H:i:s'));
        $this->assertSame('2026-02-17 06:30:00', $attendance->check_out_time->format('Y-m-d H:i:s'));
        $this->assertSame('8.50', (string) $attendance->total_hours);
        $this->assertSame('0.50', (string) $attendance->overtime_hours);
        $this->assertSame('on_time', $attendance->status);
    }

    public function test_process_logs_preserves_manual_leave_status_without_times(): void
    {
        $user = User::factory()->create();
        $machine = MesinFinger::factory()->active()->create();

        EmployeeAttendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-16',
            'status' => 'leave',
            'notes' => 'Cuti disetujui SDM',
        ]);

        AttendanceLog::factory()->mapped($user->id)->atDateTime('2026-02-16 08:00:00')->create([
            'mesin_finger_id' => $machine->id,
            'status' => 0,
            'verify' => 1,
        ]);

        (new AttendanceService())->processLogs('2026-02-16', '2026-02-16');

        $attendance = EmployeeAttendance::first();

        $this->assertSame('leave', $attendance->status);
        $this->assertNull($attendance->check_in_time);
        $this->assertSame('Cuti disetujui SDM', $attendance->notes);
    }
}
