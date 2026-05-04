# Application Architecture and Logic Audit

**Date:** 2026-05-04

**Scope:** Laravel app routes, Livewire modules, controllers, models, services, payroll, SDM attendance/shift, fingerprint/ADMS sync, authorization, and high-risk operations.

**Constraint:** This audit is read-first. No production database reset, no `migrate:fresh`, and no implementation fixes should happen until findings are reviewed and approved.

## Architecture Summary

The application is a Laravel app with role-based route areas and Livewire-heavy administration screens.

Major areas:

- **Superadmin:** user/role management, activity logs, attendance logs, system services, operations console, SMTP/email logs, fingerprint logs.
- **SDM:** employee/dosen master data, slip gaji/payroll, attendance, holidays, settings, shifts, fingerprint views.
- **Sekretariat:** surat keputusan, kegiatan pejabat, announcements.
- **Staff:** dashboard, profile, payroll self-service, attendance history, announcements.

Important route ownership:

- `routes/web.php:52-104` contains superadmin routes protected by `role:super-admin`.
- `routes/web.php:106-181` contains SDM routes protected by `role:super-admin|admin-sdm|sekretariat`.
- `routes/web.php:183-209` contains sekretariat routes, but the group only has `auth` and not all children have role middleware.
- `routes/web.php:211-242` contains staff routes protected by `role:staff`.

Primary domain models:

- Identity: `App\Models\User`, Spatie roles/permissions, `ActivityLog`.
- HR master: `Employee`, `Dosen`.
- Attendance: `AttendanceLog`, `Employee\Attendance`, `AttendanceSetting`, `WorkShift`, `EmployeeShiftAssignment`, `Holiday`, `MesinFinger`.
- Payroll: `SlipGajiHeader`, `SlipGajiDetail`, `EmailLog`.
- Sync/system: `SyncRun`, `SyncRunItem`, `SystemService`, sync services/jobs.

## Critical Findings

### 1. Staff Can Download Unpublished Salary Slips

**Severity:** Critical

**Files:**

- `app/Http/Controllers/SDM/SlipGajiController.php:663-676`
- `routes/web.php:219-223`, `routes/web.php:237-241`

**Root Cause:** `staffDownloadPdf($header_id)` only checks `header_id` and authenticated user's `nip`. It does not require `SlipGajiHeader.status` to be published.

**Impact:** Staff can access their own draft/unpublished payroll PDF if they know or guess a `header_id`, bypassing publish approval.

**Recommended Fix:** Require the parent header to be `STATUS_PUBLISHED` before fetching the detail. Return `404` or `403` for unpublished payroll headers.

### 2. Staff Dashboard Can Expose Another Employee Salary Through Fuzzy Name Matching

**Severity:** Critical

**Files:**

- `app/Http/Controllers/Employee/DashboardController.php:73-106`
- Related identity fallback: `app/Models/User.php:84-107`

**Root Cause:** If salary is not found by exact NIP, dashboard logic searches another user using partial first/last name and uses that user's NIP.

**Impact:** Staff can see another employee's latest salary if names partially overlap. This is a payroll confidentiality breach.

**Recommended Fix:** Remove fuzzy name fallback. Payroll access must only use trusted exact linkage: authenticated user ID, verified employee/dosen ID, or exact normalized NIP.

### 3. Payroll Import Leaves DB Transaction Open On Early Returns

**Severity:** Critical

**Files:**

- `app/Services/SlipGajiService.php:43-56`
- `app/Services/SlipGajiService.php:140-163`

**Root Cause:** `DB::beginTransaction()` is called before duplicate/header/empty-data checks, then methods return without rollback.

**Impact:** Open transactions can cause locks, inconsistent reads, and later writes unexpectedly committed/rolled back in reused connections or workers.

**Recommended Fix:** Move precondition checks before `DB::beginTransaction()`, or wrap the write section in `DB::transaction()`.

### 4. Sensitive User Data Can Be Logged, Including Plaintext Passwords

**Severity:** Critical

**Files:**

- `app/Livewire/Superadmin/UserForm.php:123-127`
- `app/Livewire/Superadmin/UserForm.php:141-145`
- `app/Services/UserService.php:110-113`
- `app/Services/UserService.php:169-173`

**Root Cause:** User creation/update arrays are logged without redaction.

**Impact:** Plaintext passwords, fingerprint PINs, NIP, role data, and account identifiers can be persisted to logs.

**Recommended Fix:** Redact `password`, `password_confirmation`, `fingerprint_pin`, tokens, and secrets before any logging. Prefer logging field names changed, not values.

### 5. Attendance Duplicate Cleanup Migration Can Delete Better Data

**Severity:** Critical/High

**File:** `database/migrations/2026_05_04_000001_add_unique_user_date_to_employee_attendances_table.php:12-23`

**Root Cause:** Duplicate `(user_id, date)` rows are deduplicated by keeping `MAX(id)` and deleting the rest without merging earliest check-in, latest checkout, manual statuses, notes, approval fields, or audit significance.

**Impact:** Irreversible attendance data loss if the newest duplicate is less complete or less correct than an older one.

**Recommended Fix:** Replace delete-only cleanup with deterministic merge or export/report duplicates for manual resolution before adding unique index.

## High Findings

### 6. Broad SDM Route Role Allows Sekretariat Payroll Management

**Files:**

- `routes/web.php:106-154`
- `app/Http/Controllers/SDM/SlipGajiController.php:27-29`

**Root Cause:** SDM payroll routes allow `sekretariat` together with `super-admin|admin-sdm`.

**Impact:** If `sekretariat` is not intended as a payroll role, it grants excessive access to confidential salary import, edit, delete, publish, export, and email actions.

**Recommended Fix:** Split payroll authorization from broad SDM route group. Use dedicated payroll permissions or restrict payroll to `super-admin|admin-sdm`.

### 7. Role Middleware Mutates Active Role Automatically

**File:** `app/Http/Middleware/CheckActiveRole.php:35-68`

**Root Cause:** Middleware switches session active role when user has one of the required roles.

**Impact:** Route authorization has side effects. Visiting a route can silently switch role context, making active-role UI restrictions unreliable.

**Recommended Fix:** Make authorization middleware side-effect free. Require explicit role switch or authorize by assigned roles only.

### 8. User Identity Resolution Uses Unsafe Partial Name Matching

**File:** `app/Models/User.php:84-107`

**Root Cause:** `employeeData` falls back to `nama LIKE %name%` when relation/NIP lookup fails.

**Impact:** User can be associated with wrong employee/dosen record, affecting profile, HR, attendance, and payroll ownership.

**Recommended Fix:** Remove partial-name auto-healing. Use exact normalized unique NIP or explicit admin reconciliation.

### 9. Fingerprint Import Is Not Idempotent/Atomic

**File:** `app/Services/FingerprintService.php:200-223`

**Root Cause:** Import manually checks for existing log, then creates/updates. There is no DB uniqueness or atomic upsert.

**Impact:** Concurrent imports can create duplicate logs, corrupt first/last tap calculations, and produce unstable attendance summaries.

**Recommended Fix:** Add database unique keys for ADMS ID or stable fallback keys, then use `upsert()`/`updateOrCreate()` safely.

### 10. Fingerprint Import Uses Hard-Coded Machine ID Fallback

**File:** `app/Services/FingerprintService.php:141-188`

**Root Cause:** Unknown device serial falls back to `mesin_finger_id = 1`.

**Impact:** Logs can be falsely attributed to machine 1 or fail if machine 1 does not exist.

**Recommended Fix:** Quarantine unknown devices, create explicit unknown-device record, or make machine nullable with review workflow. Do not silently assign to ID 1.

### 11. Payroll Amount Parsing Misreads Indonesian Dot Thousand Format

**Files:**

- `app/Imports/SlipGajiImport.php:219-245`
- `app/Imports/SlipGajiArrayImport.php:138-155`

**Root Cause:** `parseNumeric()` does not properly detect dot-only thousand separators like `1.234.567`.

**Impact:** Payroll values can be imported as `1.234` instead of `1234567`.

**Recommended Fix:** Detect `/^-?\d{1,3}(\.\d{3})+$/` and remove dots before numeric cast. Add import validation cases.

### 12. Salary Totals Are Trusted From Excel/Manual Input

**Files:**

- `app/Imports/SlipGajiImport.php:99-133`
- `app/Imports/SlipGajiArrayImport.php:40-74`
- `app/Http/Controllers/SDM/SlipGajiController.php:385-423`

**Root Cause:** Gross/net totals are accepted directly and not reconciled against component sums.

**Impact:** Formula errors or tampered Excel values can produce incorrect salary slips.

**Recommended Fix:** Centralize salary calculation server-side and validate/reconcile imported totals.

### 13. Duplicate Payroll NIP Per Header Is Not Prevented

**Files:**

- `app/Imports/SlipGajiImport.php:93-136`
- `app/Services/SlipGajiService.php:165-224`
- `database/migrations/2025_09_14_153845_create_slip_gaji_detail_table.php:14-63`

**Root Cause:** No unique constraint on `(header_id, nip)` and imports do not reject duplicate NIPs.

**Impact:** One person can get multiple salary detail rows for the same period/mode.

**Recommended Fix:** Detect duplicates before import/update and add a unique index after cleanup.

### 14. Manual Attendance Corrections Still Need Overnight Checkout Handling

**File:** `app/Livewire/Sdm/EmployeeAttendanceManagement.php:558-593`

**Root Cause:** Manual checkout time is built from the attendance date regardless of cross-midnight shift.

**Impact:** Night-shift manual corrections can store checkout on the wrong date.

**Recommended Fix:** Resolve checkout date from effective shift; if shift crosses midnight and checkout time is earlier than check-in/shift start, add one day.

### 15. Reprocessing Can Overwrite Manual Leave/Sick/Permission Corrections

**Files:**

- `app/Services/AttendanceService.php:71-100`
- `app/Services/AttendanceService.php:265-267`
- `app/Livewire/Sdm/EmployeeAttendanceManagement.php:908-915`

**Root Cause:** Automated processing recalculates existing attendance status unconditionally.

**Impact:** Manual SDM corrections can be silently replaced during force reprocess or PIN remapping.

**Recommended Fix:** Add source/manual lock metadata, and skip protected manual statuses unless explicitly overwriting corrections.

## Medium Findings

### 16. Sekretariat Surat Keputusan Route Only Requires Auth

**File:** `routes/web.php:184-193`

**Impact:** Any authenticated user can access Surat Keputusan page if the component/view exposes data.

**Recommended Fix:** Add `role:sekretariat|admin-sekretariat|super-admin` or a dedicated read-only staff route.

### 17. Profile Self-Delete Can Remove Privileged Accounts

**Files:**

- `routes/web.php:45`
- `app/Http/Controllers/ProfileController.php:43-58`

**Impact:** Sole admin/super-admin can delete their own account and cause lockout.

**Recommended Fix:** Block self-delete for privileged roles or require another super-admin approval.

### 18. Shift Default Updates Are Not Atomic

**Files:**

- `app/Livewire/Sdm/WorkShiftManager.php:124-147`
- `app/Livewire/Sdm/WorkShiftManager.php:217-234`

**Impact:** Failed saves or concurrent admins can leave zero/multiple default shifts.

**Recommended Fix:** Wrap default changes in DB transaction and consider DB-level invariant.

### 19. Sync Lock Is Released Before Queued Sync Job Runs

**File:** `app/Services/Sync/SyncOrchestratorService.php:47-70`

**Impact:** Multiple sync jobs for same mode can run concurrently.

**Recommended Fix:** Acquire/release lock inside `RunSdmSyncJob::handle()` or use Laravel unique/without-overlapping job middleware.

### 20. Sync Run Claim Is Not Atomic

**File:** `app/Jobs/Sync/RunSdmSyncJob.php:24-35`

**Impact:** Two workers can both process the same pending run.

**Recommended Fix:** Use conditional atomic update from `pending` to `running` and only continue when exactly one row was claimed.

### 21. Attendance Preprocessing Service Is Legacy/Duplicate and Unsafe

**File:** `app/Services/AttendancePreprocessingService.php`

**Root Cause:** It groups by raw calendar date, deletes/recreates inside a large transaction, swallows group errors, and differs from `AttendanceService`.

**Impact:** Data loss or inconsistent attendance logic if invoked.

**Recommended Fix:** Deprecate or rewrite to delegate to canonical `AttendanceService`.

## Cross-Cutting Observations

- Role names are inconsistent: `sekretariat` vs `admin-sekretariat` appears across routes/controllers.
- Superadmin and SDM expose overlapping attendance routes/components.
- Several legacy/dead modules likely exist, including older employee attendance controller paths and backup Blade files.
- Dangerous operations exist in web UI: attendance truncate, operations console commands, system service toggles.
- Identity resolution is repeatedly based on mutable or ambiguous identifiers: name, NIP variants, fingerprint PIN, employee_id.

## Recommended Remediation Order

### Phase 1: Confidentiality and Authorization

1. Enforce published status for staff payroll PDF download.
2. Remove fuzzy name matching from staff dashboard and `User::employeeData`.
3. Redact password/fingerprint PIN/user sensitive fields from logs.
4. Restrict payroll routes from broad `sekretariat` access unless explicitly approved.
5. Protect `/sekretariat/surat-keputusan` with role middleware.

### Phase 2: Payroll Integrity

1. Fix open transaction early returns in `SlipGajiService`.
2. Fix Indonesian numeric parsing.
3. Add salary total reconciliation.
4. Add duplicate NIP validation and later DB unique constraint after data cleanup.
5. Move pending-email deletion guard into `SlipGajiService::cancelImport()`.

### Phase 3: Attendance and Shift Integrity

1. Replace duplicate attendance cleanup migration with safe merge/report strategy.
2. Add manual attendance overnight checkout handling.
3. Preserve manual leave/sick/permission corrections during reprocess.
4. Finish cross-midnight helper consistency in overtime display and early leave helper.
5. Deprecate or unify `AttendancePreprocessingService`.

### Phase 4: Fingerprint and Sync Idempotency

1. Remove hard-coded `mesin_finger_id = 1` fallback.
2. Add idempotent unique keys/upserts for attendance logs.
3. Clear/reprocess `processed_at` when imported logs materially change.
4. Move sync lock into queued job and make run claim atomic.

### Phase 5: Architecture Cleanup

1. Define canonical role matrix and constants.
2. Consolidate duplicate attendance routes/components.
3. Mark/remove dead controllers/components after route/reference check.
4. Add explicit authorization checks inside dangerous Livewire methods.

## Implementation Plan Policy

Before implementing each phase:

- Write a focused failing test where safe and possible.
- Do not run `migrate:fresh` on local/public data.
- For database changes, create non-destructive migrations and backup/report affected rows first.
- Present destructive or data-cleanup steps for approval before running them.
