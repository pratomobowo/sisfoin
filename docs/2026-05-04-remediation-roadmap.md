# Application Remediation Roadmap

**Date:** 2026-05-04

**Goal:** Resolve all known critical-to-low logic, security, data integrity, payroll, attendance, fingerprint, and architecture issues in a safe sequence that reduces future regressions.

**Execution Rule:** Do not run `migrate:fresh`. Do not run destructive cleanup against local/public data without explicit approval. For every risky data change, produce a report/backup path first.

## Guiding Principles

- Fix confidentiality and wrong-recipient risks before convenience bugs.
- Establish one canonical identity resolver before fixing modules that depend on identity.
- Establish safe audit/report flows before destructive migrations or cleanup.
- Make queue jobs deterministic by using immutable snapshots where appropriate.
- Prefer small, reviewable changes with targeted tests where safe.
- Avoid introducing database constraints until existing dirty data is detected, reported, and cleaned safely.

## Phase 0: Stabilize Current Work In Progress

### Task 0.1: Review Existing Uncommitted Attendance/Shift Changes

**Priority:** Critical

**Why first:** Current worktree already contains changes. We need to avoid stacking new fixes on top of uncertain changes.

**Files:**

- `app/Services/AttendanceService.php`
- `app/Models/Employee/Attendance.php`
- `app/Livewire/Sdm/EmployeeAttendanceManagement.php`
- `app/Livewire/Sdm/UnitShiftCalendar.php`
- `app/Livewire/Sdm/WorkShiftManager.php`
- `app/Models/WorkShift.php`
- `tests/Feature/AttendanceServiceShiftTest.php`
- `tests/Feature/Livewire/Sdm/EmployeeAttendanceManagementSafetyTest.php`
- `database/migrations/2026_05_04_000001_add_unique_user_date_to_employee_attendances_table.php`

**Acceptance Criteria:**

- Decide which changes stay, which need revision.
- Replace unsafe duplicate-attendance migration strategy before it is used.
- No unrelated `dokumentasi/zklibrary` changes touched.

### Task 0.2: Replace Unsafe Attendance Duplicate Migration With Report/Merge Plan

**Priority:** Critical

**Why:** Existing migration keeps `MAX(id)` and deletes other attendance duplicates. That can delete better data.

**Files:**

- Modify: `database/migrations/2026_05_04_000001_add_unique_user_date_to_employee_attendances_table.php`
- Possibly create: `app/Console/Commands/ReportDuplicateAttendances.php`

**Acceptance Criteria:**

- Migration does not silently delete duplicate attendance rows.
- A command/report can show duplicate `(user_id, date)` rows with check-in/check-out/status/notes.
- Unique constraint is only added after duplicates are resolved safely.

## Phase 1: Payroll Confidentiality and Wrong Recipient Prevention

### Task 1.1: Enforce Published Status For Staff Slip Downloads

**Priority:** Critical

**Why:** Stops staff from downloading draft/unpublished salary slips.

**Files:**

- `app/Http/Controllers/SDM/SlipGajiController.php`

**Acceptance Criteria:**

- Staff can only download PDF when parent `SlipGajiHeader.status` is published.
- Draft/unpublished header returns 403/404.
- Admin preview/download remains unaffected.

### Task 1.2: Remove Fuzzy Payroll Lookup From Staff Dashboard

**Priority:** Critical

**Why:** Prevents staff from seeing another employee salary based on similar name.

**Files:**

- `app/Http/Controllers/Employee/DashboardController.php`

**Acceptance Criteria:**

- Salary lookup uses only authenticated user's trusted identifiers.
- Missing NIP/link shows safe empty state.
- No fallback to another user's name/NIP.

### Task 1.3: Remove Unsafe Partial-Name Auto-Healing From User Identity

**Priority:** Critical

**Why:** Identity resolver influences HR, payroll, attendance, and profile data.

**Files:**

- `app/Models/User.php`

**Acceptance Criteria:**

- `employeeData` does not use `LIKE %name%`.
- Exact NIP or explicit linked `employee_type/employee_id` only.
- Ambiguous/missing identity returns null, not a guessed person.

### Task 1.4: Fix Slip Gaji Email Recipient Snapshot

**Priority:** Critical

**Why:** Prevents queue job from sending to a different email than the one validated when queued.

**Files:**

- `app/Services/SlipGajiEmailService.php`
- `app/Jobs/SendSlipGajiEmailJob.php`
- `app/Models/SlipGajiDetail.php`

**Acceptance Criteria:**

- Email job sends to `EmailLog.to_email`, not recalculated live relation email.
- Subject/name can still use detail data, but recipient address is immutable from log.
- Retry uses the same logged recipient unless explicitly revalidated through a new action.

### Task 1.5: Add Strict Payroll Recipient Resolver

**Priority:** Critical

**Why:** Prevents slip gaji from being sent to the wrong employee when NIP is missing, duplicated, or ambiguous.

**Files:**

- Create: `app/Services/PayrollRecipientResolver.php`
- Modify: `app/Services/SlipGajiEmailService.php`
- Modify: `app/Jobs/SendSlipGajiEmailJob.php`
- Modify: `app/Models/SlipGajiDetail.php` if helper cleanup is needed

**Acceptance Criteria:**

- Exactly one recipient person must resolve for a slip detail.
- If no person, no email, duplicate NIP, or employee+dosen ambiguity exists, email is not queued.
- Invalid recipient reason is returned and visible in bulk result.
- Resolver considers `nip` and `nip_pns` intentionally, not accidentally.

### Task 1.6: Redact Sensitive User Data From Logs

**Priority:** Critical

**Why:** Prevents plaintext credentials/fingerprint PIN from entering logs.

**Files:**

- `app/Livewire/Superadmin/UserForm.php`
- `app/Services/UserService.php`

**Acceptance Criteria:**

- Passwords, password confirmations, fingerprint PINs, tokens, and secrets are never logged.
- Logs show actor, target user ID, and changed field names only.

## Phase 2: Authorization and Role Boundary Hardening

### Task 2.1: Restrict Payroll Routes To Payroll-Authorized Roles

**Priority:** High

**Why:** Payroll should not inherit broad SDM/sekretariat access accidentally.

**Files:**

- `routes/web.php`
- `app/Http/Controllers/SDM/SlipGajiController.php`

**Acceptance Criteria:**

- Payroll management routes are limited to `super-admin|admin-sdm` unless a dedicated payroll role is approved.
- Staff self-service routes remain view-only.

### Task 2.2: Protect Sekretariat Surat Keputusan Route

**Priority:** High

**Why:** Current route group only requires auth.

**Files:**

- `routes/web.php`

**Acceptance Criteria:**

- `/sekretariat/surat-keputusan` requires an approved sekretariat/admin role.
- If staff read-only access is needed, it is separated and filtered.

### Task 2.3: Make Role Middleware Side-Effect Free

**Priority:** High

**Why:** Authorization should not mutate active role automatically.

**Files:**

- `app/Http/Middleware/CheckActiveRole.php`
- Possibly `app/Http/Controllers/RoleController.php`

**Acceptance Criteria:**

- Middleware does not call `setActiveRole()`.
- Explicit role switching still works.
- Routes authorize predictably by assigned role or explicitly selected active role.

### Task 2.4: Block Privileged Profile Self-Delete

**Priority:** Medium

**Files:**

- `app/Http/Controllers/ProfileController.php`

**Acceptance Criteria:**

- Super-admin/admin roles cannot delete their own account through profile self-delete.
- Staff self-delete behavior is explicitly decided.

### Task 2.5: Add Local Authorization Guards To Dangerous Livewire Actions

**Priority:** Medium

**Files:**

- `app/Livewire/Superadmin/SystemServices.php`
- `app/Livewire/Superadmin/OperationsConsole.php`
- `app/Livewire/Sdm/EmployeeAttendanceManagement.php`
- Other destructive Livewire components discovered during implementation

**Acceptance Criteria:**

- Dangerous actions check role/permission inside the action method, not only at route level.
- Destructive actions require clear confirmation and audit log.

## Phase 3: Payroll Data Integrity

### Task 3.1: Fix Payroll Import Transaction Boundaries

**Priority:** Critical

**Files:**

- `app/Services/SlipGajiService.php`

**Acceptance Criteria:**

- No early return happens after `DB::beginTransaction()` without rollback.
- Prefer `DB::transaction()` for write sections.
- Duplicate and precondition checks happen before transaction where possible.

### Task 3.2: Fix Indonesian Numeric Parsing

**Priority:** High

**Files:**

- `app/Imports/SlipGajiImport.php`
- `app/Imports/SlipGajiArrayImport.php`

**Acceptance Criteria:**

- `1.234.567` parses as `1234567`.
- `1.234,56` parses as `1234.56`.
- `1,234.56` parses as `1234.56`.
- Plain numbers still work.

### Task 3.3: Add Payroll Total Reconciliation

**Priority:** High

**Files:**

- Create: `app/Services/PayrollCalculationService.php`
- Modify: imports and manual update controller

**Acceptance Criteria:**

- Server calculates gross, total deductions, and net pay.
- Import/manual values are rejected or warned when inconsistent beyond tolerance.
- Calculation logic is centralized.

### Task 3.4: Detect Duplicate NIP In Payroll Import Before Write

**Priority:** High

**Files:**

- `app/Services/SlipGajiService.php`
- `app/Imports/SlipGajiImport.php`
- `app/Imports/SlipGajiArrayImport.php`

**Acceptance Criteria:**

- Duplicate incoming NIP rows fail with row numbers.
- Existing duplicate detail rows under same header are reported before update.

### Task 3.5: Add Safe Unique Constraint For Payroll Detail Header/NIP

**Priority:** Medium

**Dependency:** Task 3.4 and duplicate cleanup report.

**Files:**

- New migration after cleanup approval.

**Acceptance Criteria:**

- `slip_gaji_detail(header_id, nip)` becomes unique after existing duplicates are resolved.
- Migration is non-destructive or refuses to run with clear error/report.

### Task 3.6: Centralize Payroll Delete/Cancel Email Queue Guard

**Priority:** Medium

**Files:**

- `app/Services/SlipGajiService.php`
- `app/Livewire/Sdm/SlipGajiManagement.php`
- `app/Http/Controllers/SDM/SlipGajiController.php`

**Acceptance Criteria:**

- All delete/cancel paths block when pending/processing email jobs exist.
- Guard lives in service, not only Livewire.

## Phase 4: Attendance and Shift Integrity

### Task 4.1: Finish Manual Overnight Attendance Correction

**Priority:** High

**Files:**

- `app/Livewire/Sdm/EmployeeAttendanceManagement.php`

**Acceptance Criteria:**

- Manual checkout for cross-midnight shift stores next-day datetime when appropriate.
- Total hours and overtime recalculate correctly.

### Task 4.2: Preserve Manual Leave/Sick/Permission During Reprocess

**Priority:** High

**Files:**

- `app/Services/AttendanceService.php`
- `app/Models/Employee/Attendance.php`
- Potential migration only if a source/locked flag is approved

**Acceptance Criteria:**

- Reprocess does not silently overwrite manually approved leave/sick/permission.
- If overwrite is needed, it requires explicit user action.

### Task 4.3: Complete Cross-Midnight Helper Consistency

**Priority:** Medium

**Files:**

- `app/Models/Employee/Attendance.php`
- `app/Livewire/Sdm/EmployeeAttendanceManagement.php`
- `app/Services/AttendanceService.php`

**Acceptance Criteria:**

- `isEarlyLeave()`, overtime display, single-tap classification, and model calculations all handle overnight shifts consistently.

### Task 4.4: Use Configured Early Arrival Threshold

**Priority:** Medium

**Files:**

- `app/Services/AttendanceService.php`
- `app/Models/WorkShift.php`

**Acceptance Criteria:**

- Attendance status uses `work_shifts.early_arrival_threshold` instead of hardcoded 20 minutes.

### Task 4.5: Deprecate Or Unify AttendancePreprocessingService

**Priority:** Medium

**Files:**

- `app/Services/AttendancePreprocessingService.php`
- Tests/commands/routes that reference it

**Acceptance Criteria:**

- Only one canonical attendance processing engine remains.
- Legacy service either delegates to `AttendanceService` or is removed from operational paths.

### Task 4.6: Make Default Shift Updates Atomic

**Priority:** Low/Medium

**Files:**

- `app/Livewire/Sdm/WorkShiftManager.php`

**Acceptance Criteria:**

- Default shift changes are in a transaction.
- A failed save cannot leave zero default shifts.

## Phase 5: Fingerprint Import and Attendance Log Idempotency

### Task 5.1: Remove Hard-Coded Machine ID Fallback

**Priority:** High

**Files:**

- `app/Services/FingerprintService.php`
- Possible migration only after approval if `mesin_finger_id` should become nullable or unknown-device record is needed

**Acceptance Criteria:**

- Unknown device logs are quarantined or explicitly marked unknown.
- Logs are not silently attached to machine ID 1.

### Task 5.2: Add Attendance Log Import Idempotency

**Priority:** High

**Files:**

- `app/Services/FingerprintService.php`
- New safe migration after duplicate report

**Acceptance Criteria:**

- Duplicate ADMS/log rows are prevented by DB key and upsert logic.
- Existing duplicates are reported before constraint is added.

### Task 5.3: Reprocess Attendance When Imported Logs Change

**Priority:** High

**Files:**

- `app/Services/FingerprintService.php`
- `app/Services/AttendanceService.php`

**Acceptance Criteria:**

- If imported log changes `user_id`, `datetime`, `pin`, or status, affected attendance dates are reprocessed.
- `processed_at` is treated as processing state, not import freshness.

### Task 5.4: Expand Overnight Processing Window After Import

**Priority:** Medium

**Files:**

- `app/Services/FingerprintService.php`
- `app/Services/AttendanceService.php`

**Acceptance Criteria:**

- Importing only next-day checkout can still update previous-day overnight attendance.

### Task 5.5: Harden ADMS Row Validation and Pagination

**Priority:** Medium

**Files:**

- `app/Services/AdmsApiService.php`
- `app/Console/Commands/PullFingerprintData.php`

**Acceptance Criteria:**

- Bad ADMS rows are quarantined/skipped with row errors, not crash entire import.
- Pagination does not under-fetch when `meta.total` is missing/unreliable.
- Command option names clearly distinguish ADMS employee ID/PIN/local user ID.

## Phase 6: Sync Job Integrity

### Task 6.1: Move Sync Lock Into The Queued Job

**Priority:** High

**Files:**

- `app/Services/Sync/SyncOrchestratorService.php`
- `app/Jobs/Sync/RunSdmSyncJob.php`

**Acceptance Criteria:**

- Lock is held while the job actually runs.
- Concurrent same-mode sync jobs cannot run.

### Task 6.2: Make Sync Run Claim Atomic

**Priority:** High

**Files:**

- `app/Jobs/Sync/RunSdmSyncJob.php`

**Acceptance Criteria:**

- Worker claims `pending -> running` with one conditional update.
- If another worker already claimed it, job exits safely.

### Task 6.3: Make Employee/Dosen Sync Writes Transactional

**Priority:** Medium

**Files:**

- `app/Services/Sync/Writers/EmployeeSyncWriter.php`
- `app/Services/Sync/Writers/DosenSyncWriter.php`

**Acceptance Criteria:**

- Per-record write is atomic.
- Duplicate external IDs/NIPs are reported and do not silently overwrite wrong records.

### Task 6.4: Prevent Silent User Link Reassignment

**Priority:** Medium

**Files:**

- `app/Services/Sync/Reconciler/UserEmployeeLinkReconciler.php`

**Acceptance Criteria:**

- Existing non-empty user employee link is not overwritten without conflict report/audit.

## Phase 7: Cleanup and Long-Term Maintainability

### Task 7.1: Define Canonical Role Matrix

**Priority:** Medium

**Files:**

- Documentation
- Routes/controllers/middleware references after approval

**Acceptance Criteria:**

- `sekretariat` vs `admin-sekretariat` is resolved.
- Every module has explicit allowed roles/permissions.

### Task 7.2: Consolidate Duplicate Routes and Legacy Modules

**Priority:** Low/Medium

**Files:**

- `routes/web.php`
- legacy controllers/components identified in audit

**Acceptance Criteria:**

- Dead controllers/components are removed or documented as legacy.
- Duplicate staff/payroll/attendance route surface is minimized.

### Task 7.3: Remove Backup Artifacts From Runtime Tree

**Priority:** Low

**Files:**

- `resources/views/dashboard.blade.php.backup`

**Acceptance Criteria:**

- Backup artifacts are removed or moved outside runtime resources.

### Task 7.4: Add Operational Runbooks

**Priority:** Low

**Files:**

- `docs/`

**Acceptance Criteria:**

- Payroll import, email resend, fingerprint import, attendance reprocess, and duplicate cleanup have safe step-by-step runbooks.

## Suggested Execution Batches

### Batch A: Immediate Security Hotfix

- Task 1.1
- Task 1.2
- Task 1.3
- Task 1.4
- Task 1.5
- Task 1.6
- Task 2.1
- Task 2.2

### Batch B: Payroll Correctness

- Task 3.1
- Task 3.2
- Task 3.3
- Task 3.4
- Task 3.6
- Task 3.5 after cleanup approval

### Batch C: Attendance/Shift Correctness

- Task 0.1
- Task 0.2
- Task 4.1
- Task 4.2
- Task 4.3
- Task 4.4
- Task 4.5
- Task 4.6

### Batch D: Fingerprint/Sync Idempotency

- Task 5.1
- Task 5.2
- Task 5.3
- Task 5.4
- Task 5.5
- Task 6.1
- Task 6.2
- Task 6.3
- Task 6.4

### Batch E: Cleanup

- Task 2.3
- Task 2.4
- Task 2.5
- Task 7.1
- Task 7.2
- Task 7.3
- Task 7.4
