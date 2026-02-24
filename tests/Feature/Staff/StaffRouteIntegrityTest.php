<?php

namespace Tests\Feature\Staff;

use App\Http\Controllers\Employee\AnnouncementController;
use App\Http\Controllers\Employee\AttendanceController;
use App\Http\Controllers\Employee\PayrollController;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;
use Tests\TestCase;

use function base_path;

class StaffRouteIntegrityTest extends TestCase
{
    public function test_staff_route_names_are_resolvable(): void
    {
        $this->assertNotEmpty(route('staff.penggajian.index', absolute: false));
        $this->assertNotEmpty(route('staff.attendance.index', absolute: false));
        $this->assertNotEmpty(route('staff.slip-gaji.index', absolute: false));
        $this->assertNotEmpty(route('staff.pengumuman.mark-as-read', ['id' => 1], absolute: false));
    }

    public function test_staff_penggajian_route_points_to_payroll_controller_index(): void
    {
        $route = Route::getRoutes()->getByName('staff.penggajian.index');

        $this->assertNotNull($route);
        $this->assertSame(PayrollController::class.'@index', $route->getActionName());
    }

    public function test_staff_attendance_route_points_to_attendance_controller_index(): void
    {
        $route = Route::getRoutes()->getByName('staff.attendance.index');

        $this->assertNotNull($route);
        $this->assertSame(AttendanceController::class.'@index', $route->getActionName());
    }

    public function test_staff_slip_gaji_index_redirects_to_penggajian_index(): void
    {
        $route = Route::getRoutes()->getByName('staff.slip-gaji.index');

        $this->assertNotNull($route);
        $this->assertSame('staff/slip-gaji', $route->uri());
    }

    public function test_staff_slip_gaji_download_pdf_route_points_to_slip_gaji_controller_method(): void
    {
        $route = Route::getRoutes()->getByName('staff.slip-gaji.download-pdf');

        $this->assertNotNull($route);
        $this->assertStringContainsString('@staffDownloadPdf', $route->getActionName());
    }

    public function test_staff_announcement_mark_as_read_route_points_to_controller_method(): void
    {
        $route = Route::getRoutes()->getByName('staff.pengumuman.mark-as-read');

        $this->assertNotNull($route);
        $this->assertSame(AnnouncementController::class.'@markAsRead', $route->getActionName());
    }

    public function test_announcement_mark_as_read_uses_framework_json_response_type(): void
    {
        $method = new ReflectionMethod(AnnouncementController::class, 'markAsRead');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertSame('Illuminate\\Http\\JsonResponse', $returnType->getName());
    }

    public function test_payroll_controller_no_longer_has_legacy_slip_methods(): void
    {
        $this->assertFalse(method_exists(PayrollController::class, 'showSlip'));
        $this->assertFalse(method_exists(PayrollController::class, 'downloadPdf'));
    }

    public function test_legacy_staff_duplicate_views_are_removed(): void
    {
        $this->assertFileDoesNotExist(base_path('resources/views/staff/penggajian/index.blade.php'));
        $this->assertFileDoesNotExist(base_path('resources/views/staff/absensi/index.blade.php'));
    }
}
