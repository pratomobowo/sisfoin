<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Employee;
use App\Models\AttendanceLog;
use App\Models\Employee\Attendance as EmployeeAttendance;
use App\Services\AttendancePreprocessingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendancePreprocessingTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new AttendancePreprocessingService();
        $this->user = User::factory()->create();
        
        // Create employee for the user
        Employee::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_processes_attendance_logs_correctly()
    {
        // Create attendance logs
        AttendanceLog::factory()->create([
            'user_id' => $this->user->id,
            'datetime' => now()->setTime(8, 0, 0), // Check-in
            'pin' => '123',
        ]);

        AttendanceLog::factory()->create([
            'user_id' => $this->user->id,
            'datetime' => now()->setTime(17, 0, 0), // Check-out
            'pin' => '123',
        ]);

        // Process the logs
        $result = $this->service->processAllAttendanceLogs();

        // Assertions
        $this->assertEquals(1, $result['processed']);
        $this->assertEquals(0, $result['errors']);
        $this->assertEquals(0, $result['skipped']);

        // Check if employee attendance was created
        $attendance = EmployeeAttendance::where('user_id', $this->user->id)->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('present', $attendance->status);
        $this->assertEquals(8.0, $attendance->total_hours);
        $this->assertEquals(0.0, $attendance->overtime_hours);
    }

    /** @test */
    public function it_handles_late_attendance_correctly()
    {
        // Create late attendance logs
        AttendanceLog::factory()->create([
            'user_id' => $this->user->id,
            'datetime' => now()->setTime(8, 30, 0), // Late check-in
            'pin' => '123',
        ]);

        AttendanceLog::factory()->create([
            'user_id' => $this->user->id,
            'datetime' => now()->setTime(17, 0, 0), // Check-out
            'pin' => '123',
        ]);

        // Process the logs
        $result = $this->service->processAllAttendanceLogs();

        // Check if attendance was marked as late
        $attendance = EmployeeAttendance::where('user_id', $this->user->id)->first();
        $this->assertEquals('late', $attendance->status);
        $this->assertStringContainsString('Terlambat', $attendance->notes);
    }

    /** @test */
    public function it_skips_logs_without_user_mapping()
    {
        // Create logs without user_id
        AttendanceLog::factory()->create([
            'user_id' => null,
            'datetime' => now()->setTime(8, 0, 0),
            'pin' => '456',
        ]);

        // Process the logs
        $result = $this->service->processAllAttendanceLogs();

        // Assertions
        $this->assertEquals(0, $result['processed']);
        $this->assertEquals(0, $result['errors']);
        $this->assertEquals(1, $result['skipped']);

        // Check if no employee attendance was created
        $attendance = EmployeeAttendance::where('user_id', $this->user->id)->first();
        $this->assertNull($attendance);
    }

    /** @test */
    public function it_calculates_overtime_correctly()
    {
        // Create logs with overtime
        AttendanceLog::factory()->create([
            'user_id' => $this->user->id,
            'datetime' => now()->setTime(8, 0, 0), // Check-in
            'pin' => '123',
        ]);

        AttendanceLog::factory()->create([
            'user_id' => $this->user->id,
            'datetime' => now()->setTime(19, 0, 0), // Late check-out (11 hours)
            'pin' => '123',
        ]);

        // Process the logs
        $result = $this->service->processAllAttendanceLogs();

        // Check if overtime was calculated correctly
        $attendance = EmployeeAttendance::where('user_id', $this->user->id)->first();
        $this->assertEquals(11.0, $attendance->total_hours);
        $this->assertEquals(3.0, $attendance->overtime_hours);
    }

    /** @test */
    public function it_handles_half_day_attendance()
    {
        // Create logs with only check-in
        AttendanceLog::factory()->create([
            'user_id' => $this->user->id,
            'datetime' => now()->setTime(8, 0, 0), // Only check-in
            'pin' => '123',
        ]);

        // Process the logs
        $result = $this->service->processAllAttendanceLogs();

        // Check if attendance was marked as half day
        $attendance = EmployeeAttendance::where('user_id', $this->user->id)->first();
        $this->assertEquals('half_day', $attendance->status);
    }

    /** @test */
    public function it_gets_attendance_log_stats_correctly()
    {
        // Create test data
        AttendanceLog::factory()->create([
            'user_id' => $this->user->id,
            'datetime' => now(),
            'pin' => '123',
        ]);

        AttendanceLog::factory()->create([
            'user_id' => null,
            'datetime' => now(),
            'pin' => '456',
        ]);

        // Get stats
        $stats = $this->service->getAttendanceLogStats();

        // Assertions
        $this->assertEquals(2, $stats['total_logs']);
        $this->assertEquals(1, $stats['mapped_logs']);
        $this->assertEquals(1, $stats['unmapped_logs']);
        $this->assertEquals(1, $stats['unique_users']);
    }

    /** @test */
    public function it_clears_all_employee_attendance()
    {
        // Create some attendance records
        EmployeeAttendance::factory()->create([
            'user_id' => $this->user->id,
        ]);

        EmployeeAttendance::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Clear all data
        $result = $this->service->clearAllEmployeeAttendance();

        // Assertions
        $this->assertEquals(2, $result['deleted']);
        $this->assertEquals(0, EmployeeAttendance::count());
    }

    /** @test */
    public function it_processes_multiple_scans_correctly()
    {
        // Create multiple scans for the same day
        AttendanceLog::factory()->create([
            'user_id' => $this->user->id,
            'datetime' => now()->setTime(7, 45, 0), // Early check-in
            'pin' => '123',
        ]);

        AttendanceLog::factory()->create([
            'user_id' => $this->user->id,
            'datetime' => now()->setTime(8, 0, 0), // Actual check-in
            'pin' => '123',
        ]);

        AttendanceLog::factory()->create([
            'user_id' => $this->user->id,
            'datetime' => now()->setTime(17, 0, 0), // Check-out
            'pin' => '123',
        ]);

        AttendanceLog::factory()->create([
            'user_id' => $this->user->id,
            'datetime' => now()->setTime(17, 15, 0), // Late check-out
            'pin' => '123',
        ]);

        // Process the logs
        $result = $this->service->processAllAttendanceLogs();

        // Check if it used the earliest check-in and latest check-out
        $attendance = EmployeeAttendance::where('user_id', $this->user->id)->first();
        $this->assertEquals('07:45', $attendance->check_in_time->format('H:i'));
        $this->assertEquals('17:15', $attendance->check_out_time->format('H:i'));
        $this->assertStringContainsString('Multiple scans', $attendance->notes);
    }
}
