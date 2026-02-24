# Staff Attendance and Announcement Hardening Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Menstabilkan modul attendance + announcement staff dengan sumber data real, persistensi read status yang benar, dan guard edge case synthesis absensi.

**Architecture:** Announcement flow dipindahkan sepenuhnya ke model `Employee\Announcement` dengan query aktif dan action `markAsRead` yang menulis ke pivot. Attendance flow dipertahankan pada controller saat ini namun parsing `working_days` dan status synthesis diperketat. Seluruh perubahan dijaga lewat regression tests targeted agar route/action/data behavior tetap konsisten.

**Tech Stack:** Laravel 12, Eloquent ORM, Blade, PHPUnit Feature Tests.

---

### Task 1: Add failing tests for real announcement data flow

**Files:**
- Create: `tests/Feature/Staff/StaffAnnouncementFlowTest.php`
- Reference: `app/Models/Employee/Announcement.php`
- Reference: `routes/web.php`

**Step 1: Write the failing tests**

```php
<?php

namespace Tests\Feature\Staff;

use App\Models\Employee\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffAnnouncementFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_announcement_index_uses_real_announcement_data(): void
    {
        $announcement = Announcement::factory()->create([
            'title' => 'Pengumuman Real Test',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $staff = User::factory()->create();
        $response = $this->actingAs($staff)->get(route('staff.pengumuman.index'));

        $response->assertOk();
        $response->assertSee('Pengumuman Real Test');
    }

    public function test_staff_mark_as_read_persists_to_pivot(): void
    {
        $announcement = Announcement::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $staff = User::factory()->create();
        $response = $this->actingAs($staff)
            ->post(route('staff.pengumuman.mark-as-read', ['id' => $announcement->id]));

        $response->assertOk();
        $this->assertDatabaseHas('announcement_reads', [
            'announcement_id' => $announcement->id,
            'user_id' => $staff->id,
        ]);
    }
}
```

**Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Staff/StaffAnnouncementFlowTest.php --testdox`
Expected: FAIL because controller still uses dummy data and `markAsRead` not persisted.

**Step 3: Commit failing test scaffold**

```bash
git add tests/Feature/Staff/StaffAnnouncementFlowTest.php
git commit -m "test: add failing staff announcement real-data flow coverage"
```

### Task 2: Migrate AnnouncementController index/show to real model queries

**Files:**
- Modify: `app/Http/Controllers/Employee/AnnouncementController.php`
- Modify: `resources/views/staff/pengumuman/index.blade.php`
- Modify: `resources/views/staff/pengumuman/show.blade.php`
- Test: `tests/Feature/Staff/StaffAnnouncementFlowTest.php`

**Step 1: Implement minimal real query for index**

- Replace dummy generator usage with:
  - `Announcement::query()->active()->orderByDesc('is_pinned')->orderByDesc('published_at')->get()`
- Build view payload shape compatible with current blade fields (`id`, `title`, `content`, `type`, `priority`, `is_pinned`, `published_at`, `attachments`, `read_status`).

**Step 2: Implement real show lookup**

- Resolve by ID from active/published announcements.
- Return 404 if not found.

**Step 3: Remove dead dummy helper methods**

- Remove `generateDummyAnnouncementData()` and `getDummyAnnouncementById()` after real query path is stable.

**Step 4: Run tests**

Run: `php artisan test tests/Feature/Staff/StaffAnnouncementFlowTest.php --testdox`
Expected: index/show tests move to PASS, mark-as-read may still FAIL until Task 3.

**Step 5: Commit**

```bash
git add app/Http/Controllers/Employee/AnnouncementController.php resources/views/staff/pengumuman/index.blade.php resources/views/staff/pengumuman/show.blade.php tests/Feature/Staff/StaffAnnouncementFlowTest.php
git commit -m "feat: switch staff announcement pages to real data source"
```

### Task 3: Persist mark-as-read into announcement_reads pivot

**Files:**
- Modify: `app/Http/Controllers/Employee/AnnouncementController.php`
- Test: `tests/Feature/Staff/StaffAnnouncementFlowTest.php`

**Step 1: Implement persistence**

- In `markAsRead(string $id): JsonResponse`:
  - fetch announcement by ID
  - if missing: return 404 JSON
  - call `$announcement->markAsReadBy(auth()->user())`
  - return success JSON

**Step 2: Re-run tests**

Run: `php artisan test tests/Feature/Staff/StaffAnnouncementFlowTest.php --testdox`
Expected: PASS.

**Step 3: Commit**

```bash
git add app/Http/Controllers/Employee/AnnouncementController.php tests/Feature/Staff/StaffAnnouncementFlowTest.php
git commit -m "fix: persist staff announcement read state to pivot"
```

### Task 4: Add failing tests for attendance synthesis hardening

**Files:**
- Create: `tests/Feature/Staff/StaffAttendanceSynthesisTest.php`
- Modify: `app/Http/Controllers/Employee/AttendanceController.php`

**Step 1: Write failing tests**

- Cover at least:
  - `working_days` parsing handles string values safely
  - non-working day without record maps to off/weekend
  - past working day without record maps to absent

**Step 2: Run tests to verify fail**

Run: `php artisan test tests/Feature/Staff/StaffAttendanceSynthesisTest.php --testdox`
Expected: FAIL on one or more synthesis/parsing edge cases.

**Step 3: Commit failing tests**

```bash
git add tests/Feature/Staff/StaffAttendanceSynthesisTest.php
git commit -m "test: add failing attendance synthesis edge-case coverage"
```

### Task 5: Harden attendance parsing and synthesis logic

**Files:**
- Modify: `app/Http/Controllers/Employee/AttendanceController.php`
- Test: `tests/Feature/Staff/StaffAttendanceSynthesisTest.php`

**Step 1: Normalize working days parsing**

- Convert configured value into integer set safely:
  - split string
  - trim each value
  - cast to int
  - filter invalid values

**Step 2: Tighten synthesis behavior**

- Ensure only valid past working day increments absent.
- Keep holiday/weekend off mapping deterministic.

**Step 3: Re-run attendance test suite**

Run: `php artisan test tests/Feature/Staff/StaffAttendanceSynthesisTest.php --testdox`
Expected: PASS.

**Step 4: Commit**

```bash
git add app/Http/Controllers/Employee/AttendanceController.php tests/Feature/Staff/StaffAttendanceSynthesisTest.php
git commit -m "fix: harden attendance synthesis and working-days parsing"
```

### Task 6: Final verification for staff attendance+announcement hardening

**Files:**
- Verify only; no required code changes

**Step 1: Run targeted full set**

Run:
- `php artisan test tests/Feature/Staff/StaffAnnouncementFlowTest.php --testdox`
- `php artisan test tests/Feature/Staff/StaffAttendanceSynthesisTest.php --testdox`
- `php artisan test tests/Feature/Staff/StaffRouteIntegrityTest.php --testdox`
- `php artisan test tests/Feature/Staff/StaffDashboardStatsTest.php --testdox`

Expected: PASS on all four suites.

**Step 2: Confirm git status**

Run: `git status --short`
Expected: only intended files changed.

**Step 3: Commit final polish if needed**

```bash
git add <remaining-files>
git commit -m "test: finalize staff attendance and announcement hardening regression coverage"
```
