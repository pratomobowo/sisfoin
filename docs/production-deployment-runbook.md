# Production Deployment Runbook

Use this runbook for deploying the remediation changes safely to production.

## Hard Rules

- Never run `migrate:fresh` in production.
- Never import local development database into production.
- Always create a full production database backup before cleanup or migration.
- Run duplicate report commands before migrations that add unique constraints.
- Do not delete production duplicate rows until the exact IDs are reviewed and approved.

## Pre-Deployment Checklist

1. Confirm the code branch is ready and reviewed.
2. Confirm maintenance window if users actively use payroll/attendance.
3. Create a full production database backup.
4. Store the backup outside the application directory.
5. Pull the latest production database into local/staging for simulation.
6. Run the full pre-migration report commands on the production copy.
7. Resolve any duplicate rows in the production copy.
8. Run `php artisan migrate` on the production copy.
9. Run smoke tests on the production copy.
10. Prepare production cleanup commands using exact IDs from production reports.

## Pre-Migration Report Commands

Run these before production migration:

```bash
php artisan slip-gaji:report-duplicate-details
php artisan attendance:report-duplicates
php artisan attendance-logs:report-duplicates
```

Expected clean output:

```text
Duplicate slip gaji detail groups: 0
Duplicate attendance groups: 0
Duplicate ADMS ID groups: 0
Duplicate PIN + datetime groups: 0
```

If any command reports duplicates, stop and resolve them before migration.

## Production Deployment Steps

1. Enable maintenance mode:

```bash
php artisan down
```

2. Backup database.

3. Pull code:

```bash
git pull
```

4. Install dependencies:

```bash
composer install --no-dev --optimize-autoloader
```

5. Clear stale caches:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

6. Run approved cleanup commands if duplicate reports require cleanup.

7. Run migrations:

```bash
php artisan migrate --force
```

8. Rebuild caches:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

9. Restart workers/services if used:

```bash
php artisan queue:restart
```

10. Disable maintenance mode:

```bash
php artisan up
```

## Smoke Tests After Deployment

- Login as super-admin.
- Login as admin-sdm.
- Login as staff.
- Open staff payroll page and confirm only published slips show.
- Try staff download for a draft slip and confirm it is denied.
- Open SDM attendance management.
- Process attendance for a small date range.
- Run duplicate report commands again.
- Run one manual sync in dry/safe mode if available.
- Confirm queue workers are healthy.

## Rollback Strategy

If migration fails before schema changes complete:

1. Keep maintenance mode enabled.
2. Read the exact migration error.
3. If duplicate guard failed, run report command and resolve duplicates.
4. Re-run migration only after the duplicate issue is resolved.

If application behavior is broken after migration:

1. Put application back in maintenance mode.
2. Restore previous code release.
3. Restore database from backup if schema/data changes cannot be safely rolled back.
4. Run smoke tests before opening access.

## New Unique Constraints Added

- `slip_gaji_detail(header_id, nip)`
- `employee_attendances(user_id, date)`
- `attendance_logs(adms_id)`
- `attendance_logs(pin, datetime)`

These migrations are intentionally guarded and non-destructive. They fail if duplicates exist.
