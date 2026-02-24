<?php

namespace Tests\Feature\Staff;

use App\Http\Controllers\Employee\DashboardController;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class StaffDashboardStatsTest extends TestCase
{
    public function test_staff_dashboard_route_points_to_employee_dashboard_controller(): void
    {
        $route = Route::getRoutes()->getByName('staff.dashboard');

        $this->assertNotNull($route);
        $this->assertSame(DashboardController::class.'@index', $route->getActionName());
    }

    public function test_staff_dashboard_view_does_not_contain_legacy_hardcoded_stats_literals(): void
    {
        $viewPath = base_path('resources/views/staff/dashboard.blade.php');
        $contents = file_get_contents($viewPath);

        $this->assertIsString($contents);
        $this->assertStringNotContainsString('<h4 class="text-primary mb-1">22</h4>', $contents);
        $this->assertStringNotContainsString('<h4 class="text-success mb-1">Rp 4.500.000</h4>', $contents);
        $this->assertStringNotContainsString('<h4 class="text-info mb-1">3</h4>', $contents);
    }

    public function test_staff_dashboard_view_uses_quick_stats_bindings_with_fallbacks(): void
    {
        $viewPath = base_path('resources/views/staff/dashboard.blade.php');
        $contents = file_get_contents($viewPath);

        $this->assertIsString($contents);
        $this->assertStringContainsString("quickStats['attendance_days_this_month']", $contents);
        $this->assertStringContainsString("quickStats['latest_net_salary']", $contents);
        $this->assertStringContainsString("quickStats['unread_announcements']", $contents);
    }
}
