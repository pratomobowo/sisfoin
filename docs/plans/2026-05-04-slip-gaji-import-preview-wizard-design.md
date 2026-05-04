# Slip Gaji Import Preview Wizard Design

## Goal

Change slip gaji upload from direct-to-database import into a multi-step wizard so SDM/admin can validate and review all Excel rows before saving data into final payroll tables.

## Current State

The active upload flow posts to `SlipGajiController::processUpload()` and immediately calls `SlipGajiService::processAndStoreImport()`. That service creates a `SlipGajiHeader`, imports Excel rows into `SlipGajiDetail`, validates imported data, and commits the transaction.

This means data enters final tables as soon as the user clicks `Upload Data`. There is no review step. The code also has an old comment stating preview/confirm methods were removed and direct import is active.

## Desired Flow

### Step 1: Upload And Metadata

User selects:

- Excel file
- Month
- Year
- Slip mode: standard, gaji 13, or THR

The button should read `Cek & Preview Data` instead of `Upload Data`.

### Step 2: Parse Into Temporary Preview

The system parses the Excel file and stores the parsed result into temporary preview tables, not final slip tables.

Validation runs during this step.

Blocking validation errors:

- Period and mode already exist in final slip headers.
- File cannot be parsed.
- Required NIP is empty.
- Duplicate NIP exists in the uploaded file.
- NIP does not match `employees.nip`, `employees.nip_pns`, `dosens.nip`, or `dosens.nip_pns`.
- Required numeric fields cannot be parsed safely.

### Step 3: Full Preview

The preview page must allow SDM/admin to inspect all rows, not only a sample.

Preview table requirements:

- Paginated.
- Searchable by NIP and name.
- Shows key columns: row number, NIP, name, core salary/allowance/deduction/net salary fields, validation status.
- Provides a row detail view or expandable raw detail for all parsed fields if the Excel structure contains many columns.

Summary section:

- Period and mode.
- Total rows.
- Valid rows.
- Error rows.
- Total net salary if available.
- Total deductions if available.
- Total gross/bruto if available.

If any blocking error exists, the `Simpan ke Database` button is disabled and the user must fix the Excel file and upload again.

### Step 4: Confirm Save

When all rows are valid and the user clicks `Simpan ke Database`:

- Re-check that period and mode still do not exist in final headers.
- Re-check that preview belongs to the current user/session.
- Create `SlipGajiHeader`.
- Copy preview rows into `SlipGajiDetail`.
- Mark preview as committed or clean it up.
- Redirect to the final slip gaji detail page.

## Temporary Tables

### `slip_gaji_import_previews`

Suggested columns:

- `id`
- `token`
- `user_id`
- `periode`
- `mode`
- `file_original`
- `status`: pending, blocked, ready, committed, cancelled, expired
- `summary_json`
- `error_count`
- `row_count`
- `expires_at`
- timestamps

### `slip_gaji_import_preview_rows`

Suggested columns:

- `id`
- `preview_id`
- `row_number`
- `nip`
- `nama`
- `net_amount`
- `gross_amount`
- `deduction_amount`
- `data_json`
- `validation_status`: valid or error
- `validation_errors_json`
- timestamps

Indexes:

- preview token unique.
- preview/user/status lookup.
- preview row pagination.
- preview row search on NIP/name.

## Service Design

Add service methods around existing parsing/import logic:

- `previewImport(UploadedFile $file, string $periode, int $userId, string $mode): array`
- `getPreview(string $token, int $userId): SlipGajiImportPreview`
- `commitPreview(string $token, int $userId): array`
- `cancelPreview(string $token, int $userId): array`

The preview parser should reuse numeric parsing and NIP precision handling from the existing import code where practical.

## Controller And Routes

Add routes:

- `POST /sdm/slip-gaji/upload/preview`
- `GET /sdm/slip-gaji/upload/preview/{token}`
- `POST /sdm/slip-gaji/upload/preview/{token}/confirm`
- `POST /sdm/slip-gaji/upload/preview/{token}/cancel`

The existing direct upload process should be replaced or redirected to the preview route so users cannot bypass the preview step.

## Non-Goals

- Do not redesign final slip PDF rendering.
- Do not change published/draft semantics beyond the import flow.
- Do not allow partial save of valid rows while invalid rows are skipped.
- Do not store preview data in final slip tables before confirmation.

## Testing

Required tests:

- Preview import creates preview records but does not create final `SlipGajiHeader` or `SlipGajiDetail`.
- Duplicate NIP blocks confirmation.
- Unmatched NIP blocks confirmation.
- Valid preview can be committed into final tables.
- Preview search can find rows by NIP/name.
- Existing direct import route no longer bypasses preview.
