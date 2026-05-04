# Slip Gaji Import Preview Wizard Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Replace direct slip gaji Excel import with a preview-and-confirm wizard that validates all rows before final database insert.

**Architecture:** Add temporary preview header/row tables and models. Parse Excel into preview rows first, expose a paginated searchable preview page, and only copy data into `slip_gaji_headers`/`slip_gaji_detail` after confirmation when all rows are valid.

**Tech Stack:** Laravel, Blade, Laravel Excel, Eloquent, PHPUnit feature tests, MySQL.

---

### Task 1: Preview Tables And Models

**Files:**
- Create migration: `database/migrations/YYYY_MM_DD_HHMMSS_create_slip_gaji_import_previews_tables.php`
- Create: `app/Models/SlipGajiImportPreview.php`
- Create: `app/Models/SlipGajiImportPreviewRow.php`
- Test: `tests/Feature/SlipGajiImportPreviewWizardTest.php`

**Step 1: Write failing model/database test**

Test that a preview can be created with rows and that no final `SlipGajiHeader` exists.

**Step 2: Run test to verify failure**

Run: `php artisan test tests/Feature/SlipGajiImportPreviewWizardTest.php --filter=preview_tables`

Expected: fail because tables/models do not exist.

**Step 3: Create migration and models**

Create `slip_gaji_import_previews` and `slip_gaji_import_preview_rows` tables according to the design doc.

**Step 4: Run test to verify pass**

Run: `php artisan test tests/Feature/SlipGajiImportPreviewWizardTest.php --filter=preview_tables`

Expected: pass.

---

### Task 2: Preview Parser Service

**Files:**
- Modify: `app/Services/SlipGajiService.php`
- Test: `tests/Feature/SlipGajiImportPreviewWizardTest.php`

**Step 1: Write failing preview import test**

Test that `previewImport()` parses an Excel file into preview rows and creates no final slip header/detail records.

**Step 2: Run test to verify failure**

Run: `php artisan test tests/Feature/SlipGajiImportPreviewWizardTest.php --filter=preview_import_creates_preview_only`

Expected: fail because `previewImport()` does not exist.

**Step 3: Implement minimal `previewImport()`**

Read Excel rows using existing array import behavior, normalize NIP/numeric values using existing parsing helpers where practical, create preview header/rows, compute summary.

**Step 4: Run test to verify pass**

Run: `php artisan test tests/Feature/SlipGajiImportPreviewWizardTest.php --filter=preview_import_creates_preview_only`

Expected: pass.

---

### Task 3: Blocking Validation

**Files:**
- Modify: `app/Services/SlipGajiService.php`
- Test: `tests/Feature/SlipGajiImportPreviewWizardTest.php`

**Step 1: Write failing validation tests**

Add tests for:

- Duplicate NIP marks preview blocked and disables commit.
- Unmatched NIP marks preview blocked and disables commit.
- Existing period/mode returns error before preview rows are created.

**Step 2: Run tests to verify failure**

Run: `php artisan test tests/Feature/SlipGajiImportPreviewWizardTest.php --filter=blocks`

Expected: fail because blocking validations are not complete.

**Step 3: Implement validations**

Set row-level `validation_status` and `validation_errors_json`, and preview-level `status`/`error_count`.

**Step 4: Run tests to verify pass**

Run: `php artisan test tests/Feature/SlipGajiImportPreviewWizardTest.php --filter=blocks`

Expected: pass.

---

### Task 4: Commit Preview Into Final Tables

**Files:**
- Modify: `app/Services/SlipGajiService.php`
- Test: `tests/Feature/SlipGajiImportPreviewWizardTest.php`

**Step 1: Write failing commit tests**

Add tests for:

- Valid preview commits to `SlipGajiHeader` and `SlipGajiDetail`.
- Blocked preview cannot commit.
- Preview belonging to another user cannot commit.

**Step 2: Run tests to verify failure**

Run: `php artisan test tests/Feature/SlipGajiImportPreviewWizardTest.php --filter=commit_preview`

Expected: fail because `commitPreview()` does not exist.

**Step 3: Implement `commitPreview()`**

Use a transaction, re-check period/mode uniqueness, create final header, copy valid preview rows into final details, and mark preview committed.

**Step 4: Run tests to verify pass**

Run: `php artisan test tests/Feature/SlipGajiImportPreviewWizardTest.php --filter=commit_preview`

Expected: pass.

---

### Task 5: Controller Routes And Views

**Files:**
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/SDM/SlipGajiController.php`
- Modify: `resources/views/sdm/slip-gaji/upload.blade.php`
- Create: `resources/views/sdm/slip-gaji/preview-upload.blade.php`
- Test: `tests/Feature/SlipGajiImportPreviewWizardTest.php`

**Step 1: Write failing HTTP flow tests**

Test that upload preview route redirects to preview page and existing direct process route does not create final rows directly.

**Step 2: Run tests to verify failure**

Run: `php artisan test tests/Feature/SlipGajiImportPreviewWizardTest.php --filter=http_flow`

Expected: fail because routes/views are missing.

**Step 3: Implement routes/controller/view**

Add preview, show preview, confirm, and cancel actions. Update upload form action/button text.

**Step 4: Run tests to verify pass**

Run: `php artisan test tests/Feature/SlipGajiImportPreviewWizardTest.php --filter=http_flow`

Expected: pass.

---

### Task 6: Paginated Searchable Preview

**Files:**
- Modify: `app/Http/Controllers/SDM/SlipGajiController.php`
- Modify: `resources/views/sdm/slip-gaji/preview-upload.blade.php`
- Test: `tests/Feature/SlipGajiImportPreviewWizardTest.php`

**Step 1: Write failing search test**

Test that preview page can filter rows by NIP/name and paginates rows.

**Step 2: Run test to verify failure**

Run: `php artisan test tests/Feature/SlipGajiImportPreviewWizardTest.php --filter=preview_search`

Expected: fail until preview query/search is implemented.

**Step 3: Implement search/pagination**

Query preview rows by token/user, add NIP/name search, paginate with query string preserved.

**Step 4: Run test to verify pass**

Run: `php artisan test tests/Feature/SlipGajiImportPreviewWizardTest.php --filter=preview_search`

Expected: pass.

---

### Task 7: Final Verification

**Files:**
- No code changes expected unless verification reveals an issue.

**Step 1: Run targeted tests**

Run: `php artisan test tests/Feature/SlipGajiImportPreviewWizardTest.php tests/Feature/PayrollIntegrityHotfixTest.php tests/Feature/SlipGajiImportNipPrecisionTest.php tests/Feature/PermissionModuleAccessTest.php`

Expected: all pass.

**Step 2: Run syntax checks**

Run `php -l` on modified PHP files.

Expected: no syntax errors.

**Step 3: Review status**

Run: `git status --short`

Expected: intended files only, excluding SQL dump and `dokumentasi/zklibrary`.
