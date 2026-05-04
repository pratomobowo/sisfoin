# Operations Runbook

This document covers recurring operational procedures for payroll, attendance, fingerprint imports, and sync.

## Payroll Operations

### Import Payroll

1. Confirm the period and mode are correct.
2. Confirm the Excel file has no duplicate NIP rows.
3. Import through SDM payroll UI.
4. Check import result summary.
5. Review invalid recipients before sending email.
6. Publish only after data is reviewed.

Important protections:

- Import rejects duplicate NIP rows.
- Server recalculates payroll totals from components.
- Staff only sees published slips.
- Email recipient is resolved strictly and snapshotted to `email_logs.to_email`.

### Send Payroll Emails

1. Use bulk email only after publish/review.
2. Review invalid recipient count.
3. Do not delete/cancel payroll while email jobs are pending or processing.
4. If an email must be retried, retry from the email log flow so the recipient is explicit.

### Duplicate Payroll Detail Check

```bash
php artisan slip-gaji:report-duplicate-details
```

If duplicates exist:

1. Compare each duplicate detail row.
2. Check whether any email log references either row.
3. Keep the correct row by explicit ID.
4. Delete only the reviewed duplicate IDs.
5. Run the report command again.

## Attendance Operations

### Process Attendance Logs

Use the attendance UI or command:

```bash
php artisan attendance:process
```

Important protections:

- Shift malam checkout can be assigned to the next day.
- Manual `sick`, `leave`, and `permission` without times are preserved during reprocess.
- One attendance row per `user_id + date` is enforced.

### Duplicate Attendance Check

```bash
php artisan attendance:report-duplicates
```

If duplicates exist:

1. Compare check-in/check-out/status/notes.
2. Preserve manual corrections and approved statuses.
3. Delete only reviewed duplicate IDs.
4. Run report again before migration or reprocess.

### Manual Attendance Correction

- For overnight shifts, enter check-in and checkout times normally.
- If checkout time is earlier than check-in time, the system stores checkout on the next day.
- After saving, total hours and overtime are recalculated.

## Fingerprint / ADMS Operations

### Pull Fingerprint Data

Recommended command:

```bash
php artisan fingerprint:pull-data --date-from=YYYY-MM-DD --date-to=YYYY-MM-DD --process
```

Filter one ADMS employee:

```bash
php artisan fingerprint:pull-data --date-from=YYYY-MM-DD --date-to=YYYY-MM-DD --adms-employee-id=ADMS_ID --process
```

Deprecated alias still works:

```bash
php artisan fingerprint:pull-data --employee-id=ADMS_ID
```

Important protections:

- Unknown device logs go to `UNKNOWN_ADMS_DEVICE`, not machine ID 1.
- ADMS bad rows are skipped with warning logs.
- Pagination continues even when `meta.total` is missing.
- Existing logs reset `processed_at` when imported data changes.

### Duplicate Raw Attendance Logs Check

```bash
php artisan attendance-logs:report-duplicates
```

If duplicates exist:

1. Prefer the latest ID if rows are identical.
2. If rows differ, compare ADMS ID, PIN, datetime, machine, user mapping, and processed state.
3. Delete only reviewed duplicate IDs.
4. Run report again.

## SDM Sync Operations

### Manual Sync

Use the UI sync button for employee/dosen sync.

Important protections:

- Sync lock is held by the queued job while it runs.
- Job claims a pending run atomically.
- Writers process each record in a transaction.
- User link reconciler does not silently move an existing user link to a different employee/dosen.

### Sync Warning Handling

If sync completes with warning:

1. Open sync run items.
2. Review `error` items for failed records.
3. Review `warning` items for link reconciliation conflicts.
4. Resolve duplicate NIP or user link conflicts manually.
5. Re-run sync after data is corrected.

## Admin Safety

- `super-admin`, `admin-sdm`, and `admin-sekretariat` cannot delete their own profile account.
- Superadmin destructive Livewire actions also check role inside the action method.
- Active role no longer auto-switches during route middleware checks.
