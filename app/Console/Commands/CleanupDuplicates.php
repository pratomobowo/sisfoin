<?php

namespace App\Console\Commands;

use App\Models\Dosen;
use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupDuplicates extends Command
{
    protected $signature = 'cleanup:duplicates 
                            {--model= : Model to clean (employee, dosen, or all)}
                            {--dry-run : Preview only, no actual changes}';

    protected $description = 'Merge and cleanup duplicate records in Employee and Dosen tables';

    public function handle(): int
    {
        $model = $this->option('model') ?? 'all';
        $dryRun = $this->option('dry-run');

        if (!in_array($model, ['employee', 'dosen', 'all'])) {
            $this->error('Invalid model. Use: employee, dosen, or all');
            return 1;
        }

        $this->info('=== Cleanup Duplicate Records ===');
        $this->info('Mode: ' . ($dryRun ? 'DRY RUN (preview only)' : 'EXECUTE'));
        $this->newLine();

        if ($dryRun) {
            $this->warn('This is a DRY RUN. No changes will be made.');
            $this->newLine();
        }

        if ($model === 'all' || $model === 'employee') {
            $this->cleanupModel(Employee::class, 'Employee', $dryRun);
        }

        if ($model === 'all' || $model === 'dosen') {
            $this->cleanupModel(Dosen::class, 'Dosen', $dryRun);
        }

        $this->newLine();
        $this->info('Cleanup completed!');

        return 0;
    }

    private function cleanupModel(string $modelClass, string $modelName, bool $dryRun): void
    {
        $this->info("--- Processing {$modelName} ---");

        // Get duplicates by id_pegawai
        $duplicatesById = $modelClass::select('id_pegawai', DB::raw('count(*) as total'))
            ->whereNotNull('id_pegawai')
            ->groupBy('id_pegawai')
            ->having('total', '>', 1)
            ->get();

        // Get duplicates by nip (for records without id_pegawai)
        $duplicatesByNip = $modelClass::select('nip', DB::raw('count(*) as total'))
            ->whereNotNull('nip')
            ->where('nip', '!=', '')
            ->whereNull('id_pegawai')
            ->groupBy('nip')
            ->having('total', '>', 1)
            ->get();

        $totalById = $duplicatesById->count();
        $totalByNip = $duplicatesByNip->count();

        $this->info("Duplicates by id_pegawai: {$totalById}");
        $this->info("Duplicates by nip (no id_pegawai): {$totalByNip}");

        if ($totalById === 0 && $totalByNip === 0) {
            $this->info("No duplicates found in {$modelName}.");
            return;
        }

        $mergedCount = 0;
        $deletedCount = 0;

        // Process duplicates by id_pegawai
        foreach ($duplicatesById as $dup) {
            $records = $modelClass::where('id_pegawai', $dup->id_pegawai)
                ->orderByDesc('id')
                ->get();

            $result = $this->mergeRecords($modelClass, $records, $dryRun);
            $mergedCount += $result['merged'];
            $deletedCount += $result['deleted'];
        }

        // Process duplicates by nip
        foreach ($duplicatesByNip as $dup) {
            $records = $modelClass::where('nip', $dup->nip)
                ->whereNull('id_pegawai')
                ->orderByDesc('id')
                ->get();

            $result = $this->mergeRecords($modelClass, $records, $dryRun);
            $mergedCount += $result['merged'];
            $deletedCount += $result['deleted'];
        }

        $this->info("Merged: {$mergedCount}, Deleted: {$deletedCount}");
    }

    private function mergeRecords(string $modelClass, $records, bool $dryRun): array
    {
        if ($records->count() <= 1) {
            return ['merged' => 0, 'deleted' => 0];
        }

        $keepRecord = $records->first();
        $toDelete = $records->skip(1);

        if ($dryRun) {
            $this->line("  Would merge {$toDelete->count()} records into ID {$keepRecord->id} (NIP: {$keepRecord->nip})");
            return ['merged' => 1, 'deleted' => $toDelete->count()];
        }

        // Merge data from duplicate records
        $mergedData = $this->getMergeData($records);

        // Update the keep record with merged data
        $keepRecord->update($mergedData);

        // Update foreign keys in related tables
        $this->updateForeignKeys($modelClass, $keepRecord, $toDelete->pluck('id')->toArray());

        // Delete duplicate records (hard delete)
        $modelClass::whereIn('id', $toDelete->pluck('id'))->forceDelete();

        Log::info("Merged duplicate records", [
            'model' => $modelClass,
            'kept_id' => $keepRecord->id,
            'deleted_ids' => $toDelete->pluck('id')->toArray(),
            'nip' => $keepRecord->nip,
        ]);

        return ['merged' => 1, 'deleted' => $toDelete->count()];
    }

    private function getMergeData($records): array
    {
        $mergedData = [];
        $fillable = $records->first()->getFillable();

        foreach ($fillable as $field) {
            // Skip timestamps and primary key
            if (in_array($field, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            // Get all non-null values for this field
            $values = $records->pluck($field)->filter(fn($v) => $v !== null && $v !== '');

            if ($values->isNotEmpty()) {
                // Priority: take from newest record (first in collection)
                $mergedData[$field] = $values->first();
            }
        }

        return $mergedData;
    }

    private function updateForeignKeys(string $modelClass, $keepRecord, array $deleteIds): void
    {
        if (empty($deleteIds)) {
            return;
        }

        // Update slip_gaji_detail
        if ($modelClass === Employee::class) {
            DB::table('slip_gaji_detail')
                ->whereIn('employee_id', $deleteIds)
                ->update(['employee_id' => $keepRecord->id]);

            // Update users table if linked
            DB::table('users')
                ->whereIn('employee_id', $deleteIds)
                ->where('employee_type', 'employee')
                ->update(['employee_id' => $keepRecord->id]);
        }

        if ($modelClass === Dosen::class) {
            DB::table('slip_gaji_detail')
                ->whereIn('dosen_id', $deleteIds)
                ->update(['dosen_id' => $keepRecord->id]);

            // Update users table if linked
            DB::table('users')
                ->whereIn('employee_id', $deleteIds)
                ->where('employee_type', 'dosen')
                ->update(['employee_id' => $keepRecord->id]);
        }
    }
}
