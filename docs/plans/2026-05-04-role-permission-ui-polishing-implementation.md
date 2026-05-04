# Role Permission UI Polishing Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Allow admins to select granular permissions per module in the role form and see module permission coverage in role management.

**Architecture:** Keep `config/modules.php` as the source of truth. Make `RoleForm::$selectedPermissions` the primary role permission state, derive module coverage from selected permissions, and update Blade views to render module cards with per-permission checkboxes.

**Tech Stack:** Laravel, Livewire, Blade, Spatie Laravel Permission, PHPUnit/Livewire testing.

---

### Task 1: Add Livewire Tests For Partial Permission Sync

**Files:**
- Create: `tests/Feature/Livewire/Superadmin/RoleFormPermissionTest.php`
- Modify: `app/Livewire/Superadmin/RoleForm.php`

**Step 1: Write failing tests**

Add tests for these behaviors:

- Creating a role with only `payroll.view` syncs only `payroll.view`.
- Editing a role with partial permissions loads `selectedPermissions`.
- Saving `staff` filters permissions to `.view` and `profile.edit`.

**Step 2: Run tests to verify failure**

Run: `php artisan test tests/Feature/Livewire/Superadmin/RoleFormPermissionTest.php`

Expected: fail because `selectedPermissions` is currently secondary and role edit loads only `selectedModules`.

**Step 3: Implement minimal RoleForm changes**

Update `RoleForm` so:

- `selectedPermissions` is validated as an array.
- `loadRoleData()` and `edit()` populate `selectedPermissions` from `$role->permissions`.
- `syncRolePermissions()` syncs `selectedPermissions` exactly.
- Staff filtering remains.
- `selectedModules` is refreshed from permissions for compatibility.

**Step 4: Run tests to verify pass**

Run: `php artisan test tests/Feature/Livewire/Superadmin/RoleFormPermissionTest.php`

Expected: pass.

---

### Task 2: Add Module Coverage Helpers

**Files:**
- Modify: `app/Livewire/Superadmin/RoleForm.php`
- Modify: `app/Livewire/Superadmin/RoleManagement.php`
- Test: `tests/Feature/Livewire/Superadmin/RoleFormPermissionTest.php`

**Step 1: Write failing tests**

Add tests for helper output:

- `getModuleSelectionCount('payroll_management')` returns selected/total counts.
- `isModuleFullySelected('payroll_management')` is true only when all permissions are selected.

**Step 2: Run tests to verify failure**

Run: `php artisan test tests/Feature/Livewire/Superadmin/RoleFormPermissionTest.php --filter=module`

Expected: fail because helpers do not exist.

**Step 3: Implement helpers**

Add public methods on `RoleForm`:

- `getModulePermissionNames(string $moduleKey): array`
- `getModuleSelectionCount(string $moduleKey): array`
- `isModuleFullySelected(string $moduleKey): bool`
- `toggleModulePermissions(string $moduleKey): void`

Add a public static helper on `RoleManagement` or compute directly in render data for coverage badges.

**Step 4: Run tests to verify pass**

Run: `php artisan test tests/Feature/Livewire/Superadmin/RoleFormPermissionTest.php`

Expected: pass.

---

### Task 3: Update Role Form UI

**Files:**
- Modify: `resources/views/livewire/superadmin/role-form.blade.php`

**Step 1: Replace module checkbox grid**

Render each module as a card containing:

- Module label.
- Selected count badge.
- Button/checkbox to select all permissions for the module.
- Permission checkboxes bound to `selectedPermissions`.

**Step 2: Keep layout minimal and consistent**

Use existing Tailwind classes and existing visual style. Do not introduce new JavaScript.

**Step 3: Run role form tests**

Run: `php artisan test tests/Feature/Livewire/Superadmin/RoleFormPermissionTest.php`

Expected: pass.

---

### Task 4: Update Role Management Table And Bulk Assign

**Files:**
- Modify: `app/Livewire/Superadmin/RoleManagement.php`
- Modify: `resources/views/livewire/superadmin/role-management.blade.php`

**Step 1: Update module badge display**

Show module badges as `Label selected/total`.

**Step 2: Add bulk permission selection**

When a bulk module is selected, show individual permission checkboxes and apply only selected permissions additively.

**Step 3: Run targeted tests**

Run: `php artisan test tests/Feature/PermissionModuleAccessTest.php tests/Feature/Livewire/Superadmin/RoleFormPermissionTest.php`

Expected: pass.

---

### Task 5: Final Verification

**Files:**
- No code changes expected unless verification reveals an issue.

**Step 1: Run targeted regression**

Run: `php artisan test tests/Feature/PermissionModuleAccessTest.php tests/Feature/Livewire/Superadmin/RoleFormPermissionTest.php tests/Feature/AuthAuthorizationHardeningTest.php`

Expected: all pass.

**Step 2: Run syntax checks**

Run `php -l` for modified PHP files.

Expected: no syntax errors.

**Step 3: Review git status**

Run: `git status --short`

Expected: intended UI polishing files only, excluding SQL dump and `dokumentasi/zklibrary`.
