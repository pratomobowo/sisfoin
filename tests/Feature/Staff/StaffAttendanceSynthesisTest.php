<?php

namespace Tests\Feature\Staff;

use App\Http\Controllers\Employee\AttendanceController;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;
use Tests\TestCase;

class StaffAttendanceSynthesisTest extends TestCase
{
    public function test_staff_attendance_route_points_to_controller_index(): void
    {
        $route = Route::getRoutes()->getByName('staff.attendance.index');

        $this->assertNotNull($route);
        $this->assertSame(AttendanceController::class.'@index', $route->getActionName());
    }

    public function test_attendance_controller_has_working_days_normalizer(): void
    {
        $this->assertTrue(method_exists(AttendanceController::class, 'normalizeWorkingDays'));
    }

    public function test_normalize_working_days_handles_string_values_safely(): void
    {
        $controller = new AttendanceController;
        $method = new ReflectionMethod(AttendanceController::class, 'normalizeWorkingDays');
        $method->setAccessible(true);

        $result = $method->invoke($controller, '1, 2,foo,8,6,2');

        $this->assertSame([1, 2, 6], $result);
    }

    public function test_normalize_working_days_returns_default_when_empty_or_invalid(): void
    {
        $controller = new AttendanceController;
        $method = new ReflectionMethod(AttendanceController::class, 'normalizeWorkingDays');
        $method->setAccessible(true);

        $result = $method->invoke($controller, ',,foo,0,9');

        $this->assertSame([1, 2, 3, 4, 5, 6], $result);
    }
}
