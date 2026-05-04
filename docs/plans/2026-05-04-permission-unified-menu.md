# Permission Unified Menu Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Make sidebar/menu visibility follow the user's effective permissions from all assigned roles, while backend route and action access remains enforced by permissions.

**Architecture:** Treat roles as permission bundles and Spatie permissions as the source of truth. Sidebar entries should use `@can` / `@canany` for effective user permissions, not `isActiveRole(...)`, except where the route remains deliberately role-only. Route/action security must continue to use `can:*` middleware or explicit `abort_unless(...->can(...))` guards.

**Tech Stack:** Laravel, Blade, Livewire, Spatie Laravel Permission, PHPUnit feature tests.

---

### Task 1: Add Regression Tests For Multi-Role Menu Behavior

**Files:**
- Modify: `tests/Feature/PermissionModuleAccessTest.php`

**Step 1: Write failing tests**

Add tests that prove a user with permissions from multiple roles sees modules from both roles in the sidebar, and a user without those permissions does not see those module links.

**Step 2: Run tests to verify failure if sidebar still role-active based**

Run: `php artisan test tests/Feature/PermissionModuleAccessTest.php --filter=sidebar`

Expected: fail if menu still depends on `active_role` or hardcoded role checks.

**Step 3: Implement minimal sidebar changes**

Replace remaining module-oriented `isActiveRole(...)` checks in `resources/views/components/sidebar.blade.php` with `@can` / `@canany` checks using effective user permissions.

**Step 4: Run tests to verify pass**

Run: `php artisan test tests/Feature/PermissionModuleAccessTest.php --filter=sidebar`

Expected: pass.

---

### Task 2: Clean Up Route Authorization Drift

**Files:**
- Modify: `routes/web.php`
- Modify tests as needed in `tests/Feature/PermissionModuleAccessTest.php`

**Step 1: Identify routes still guarded by role where permission exists**

Search for `role:` and `isActiveRole(` in `routes/web.php`.

**Step 2: Write failing permission-based access test for each route family changed**

Use custom roles with only the required permission.

**Step 3: Replace route middleware with `can:*` where the route represents a permissioned module**

Do not change routes that are explicitly superadmin-only unless a matching permission and UI path exists.

**Step 4: Run targeted tests**

Run: `php artisan test tests/Feature/PermissionModuleAccessTest.php tests/Feature/AuthAuthorizationHardeningTest.php`

Expected: pass.

---

### Task 3: Verify Action-Level Permission Guards

**Files:**
- Modify Livewire/controller methods only if an action can mutate data without a permission check.

**Step 1: Search mutable methods**

Search for `save`, `delete`, `destroy`, `update`, `publish`, `unpublish`, `send`, `retry`, `clear`, and `process` in SDM/Sekretariat Livewire/controllers.

**Step 2: Add minimal guards**

Use `abort_unless(auth()->user()?->can('<permission>'), 403);` at the start of mutating methods where missing.

**Step 3: Run targeted tests**

Run: `php artisan test tests/Feature/PermissionModuleAccessTest.php tests/Feature/Livewire/Sdm/EmployeeAttendanceManagementSafetyTest.php`

Expected: pass.

---

### Task 4: Final Verification

**Files:**
- No code changes expected unless verification reveals an issue.

**Step 1: Run targeted regression suite**

Run: `php artisan test tests/Feature/PermissionModuleAccessTest.php tests/Feature/AuthAuthorizationHardeningTest.php tests/Feature/BatchASecurityHotfixTest.php tests/Feature/StaffPayrollFilterTest.php tests/Feature/Livewire/Sdm/EmployeeAttendanceManagementSafetyTest.php`

Expected: all tests pass.

**Step 2: Run syntax checks**

Run `php -l` on every modified PHP file.

Expected: no syntax errors.

**Step 3: Review git status**

Run: `git status --short`

Expected: only intended Batch I files are modified/untracked; SQL dump and `dokumentasi/zklibrary` remain excluded.
