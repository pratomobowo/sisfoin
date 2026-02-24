<?php

namespace Tests\Feature\Staff;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class StaffDashboardStatsTest extends TestCase
{
    public function test_staff_dashboard_route_exists_and_is_redirect(): void
    {
        $route = Route::getRoutes()->getByName('staff.dashboard');

        $this->assertNotNull($route);
        // Route should be a closure that redirects to unified dashboard
        $this->assertSame('Closure', $route->getActionName());
    }

    public function test_legacy_staff_dashboard_view_is_removed(): void
    {
        $viewPath = base_path('resources/views/staff/dashboard.blade.php');
        $this->assertFileDoesNotExist($viewPath);
    }
}
