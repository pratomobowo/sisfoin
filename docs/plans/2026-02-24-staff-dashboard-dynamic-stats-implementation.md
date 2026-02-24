# Staff Dashboard Dynamic Stats Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Mengganti ringkasan cepat dashboard staff dari angka hardcoded menjadi metrik data real per user login dengan fallback aman.

**Architecture:** Route `staff.dashboard` akan diarahkan ke `Employee\DashboardController@index` agar dashboard staff punya entry point khusus. Controller akan menghitung `quickStats` dari model attendance, slip gaji, dan announcement aktif-belum-dibaca, lalu view staff dashboard merender nilai dinamis dengan fallback tanpa error.

**Tech Stack:** Laravel 11, Eloquent ORM, Blade view, PHPUnit Feature Tests.

---

### Task 1: Add failing tests for staff dashboard route ownership and fallback rendering

**Files:**
- Create: `tests/Feature/Staff/StaffDashboardStatsTest.php`
- Reference: `routes/web.php`

**Step 1: Write the failing tests**

```php
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
        $this->assertStringNotContainsString('22', $contents);
        $this->assertStringNotContainsString('Rp 4.500.000', $contents);
        $this->assertStringNotContainsString('3', $contents);
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
```

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Staff/StaffDashboardStatsTest.php --testdox`
Expected: FAIL karena route `staff.dashboard` masih redirect closure dan view masih berisi angka hardcoded.

**Step 3: Commit failing tests**

```bash
git add tests/Feature/Staff/StaffDashboardStatsTest.php
git commit -m "test: add failing staff dashboard stats coverage"
```

### Task 2: Switch staff dashboard route to dedicated controller action

**Files:**
- Modify: `routes/web.php`
- Test: `tests/Feature/Staff/StaffDashboardStatsTest.php`

**Step 1: Update route mapping**

Change within `staff` route group:

```php
Route::get('/', [App\Http\Controllers\Employee\DashboardController::class, 'index'])->name('dashboard');
```

Replace current redirect closure route.

**Step 2: Run tests to verify partial progress**

Run: `php artisan test tests/Feature/Staff/StaffDashboardStatsTest.php --testdox`
Expected: Route mapping test PASS, view hardcoded tests still FAIL.

**Step 3: Commit route update**

```bash
git add routes/web.php tests/Feature/Staff/StaffDashboardStatsTest.php
git commit -m "fix: route staff dashboard to employee controller"
```

### Task 3: Implement quickStats aggregation in Employee DashboardController

**Files:**
- Modify: `app/Http/Controllers/Employee/DashboardController.php`
- Reference: `app/Models/Employee/Attendance.php`
- Reference: `app/Models/SlipGajiDetail.php`
- Reference: `app/Models/Employee/Announcement.php`

**Step 1: Add required imports**

```php
use App\Models\Employee\Announcement;
use App\Models\Employee\Attendance;
use App\Models\SlipGajiDetail;
use Illuminate\Support\Carbon;
```

**Step 2: Add minimal stats queries in `index()`**

Compute:
- `attendance_days_this_month`: count attendance this month for auth user with statuses `present`, `on_time`, `early_arrival`, `late`.
- `latest_net_salary`: latest `penerimaan_bersih` from `SlipGajiDetail` by current user NIP and `status = 'Tersedia'`.
- `unread_announcements`: `Announcement::query()->active()->unreadBy($user)->count()`.

Use safe defaults when no user/NIP/data available.

**Step 3: Pass `quickStats` payload to view**

```php
'quickStats' => [
    'attendance_days_this_month' => $attendanceDaysThisMonth,
    'latest_net_salary' => $latestNetSalary,
    'unread_announcements' => $unreadAnnouncements,
],
```

**Step 4: Run tests**

Run: `php artisan test tests/Feature/Staff/StaffDashboardStatsTest.php --testdox`
Expected: Route test PASS, view literal tests may still FAIL until blade is updated.

**Step 5: Commit controller logic**

```bash
git add app/Http/Controllers/Employee/DashboardController.php
git commit -m "feat: add dynamic quick stats for staff dashboard"
```

### Task 4: Replace hardcoded dashboard stats in Blade with quickStats bindings

**Files:**
- Modify: `resources/views/staff/dashboard.blade.php`
- Test: `tests/Feature/Staff/StaffDashboardStatsTest.php`

**Step 1: Replace hardcoded values with bindings**

Replace:
- `22` -> `{{ $quickStats['attendance_days_this_month'] ?? 0 }}`
- `Rp 4.500.000` -> conditional formatter from `latest_net_salary` with fallback `Belum tersedia`
- `3` -> `{{ $quickStats['unread_announcements'] ?? 0 }}`

**Step 2: Add salary fallback rendering**

```php
@if(!is_null($quickStats['latest_net_salary'] ?? null))
    Rp {{ number_format($quickStats['latest_net_salary'], 0, ',', '.') }}
@else
    Belum tersedia
@endif
```

**Step 3: Run tests to green**

Run: `php artisan test tests/Feature/Staff/StaffDashboardStatsTest.php --testdox`
Expected: PASS.

**Step 4: Commit blade update**

```bash
git add resources/views/staff/dashboard.blade.php tests/Feature/Staff/StaffDashboardStatsTest.php
git commit -m "fix: replace staff dashboard hardcoded stats with dynamic bindings"
```

### Task 5: Final verification

**Files:**
- Verify only; no required file changes

**Step 1: Run focused suite for this feature**

Run:
- `php artisan test tests/Feature/Staff/StaffDashboardStatsTest.php --testdox`
- `php artisan test tests/Feature/Staff/StaffRouteIntegrityTest.php --testdox`

Expected: PASS for both suites.

**Step 2: Confirm git status clean or expected**

Run: `git status --short`
Expected: no unexpected modified files.

**Step 3: Commit if any remaining docs/tests adjustments**

```bash
git add <remaining-files>
git commit -m "test: finalize staff dashboard dynamic stats regression coverage"
```
