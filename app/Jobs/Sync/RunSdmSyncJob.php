<?php

namespace App\Jobs\Sync;

use App\Models\SyncRun;
use App\Models\SyncRunItem;
use App\Services\SevimaApiService;
use App\Services\Sync\Reconciler\UserEmployeeLinkReconciler;
use App\Services\Sync\Writers\DosenSyncWriter;
use App\Services\Sync\Writers\EmployeeSyncWriter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunSdmSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $syncRunId) {}

    public function handle(): void
    {
        $run = SyncRun::find($this->syncRunId);

        if (! $run || $run->status !== 'pending') {
            return;
        }

        $run->update([
            'status' => 'running',
            'started_at' => \now(),
        ]);

        try {
            if ($run->mode === 'dosen') {
                $this->handleDosenSync($run);
                $this->handleReconcile($run, 'dosen');
            } elseif ($run->mode === 'employee') {
                $this->handleEmployeeSync($run);
                $this->handleReconcile($run, 'employee');
            } elseif ($run->mode === 'all') {
                $this->handleEmployeeSync($run);
                $this->handleReconcile($run, 'employee');
                $run->refresh();
                $this->handleDosenSync($run);
                $this->handleReconcile($run, 'dosen');
            }

            $run->refresh();
            $hasReconcileConflicts = (int) ($run->error_summary['reconcile']['conflict_count'] ?? 0) > 0;
            $hasSyncErrors = (int) ($run->error_summary['error_count'] ?? 0) > 0;
            $finalStatus = ($hasReconcileConflicts || $hasSyncErrors) ? 'completed_with_warning' : 'completed';

            $run->update([
                'status' => $finalStatus,
                'finished_at' => \now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('RunSdmSyncJob failed', [
                'sync_run_id' => $run->id,
                'error' => $e->getMessage(),
            ]);

            $run->update([
                'status' => 'failed',
                'error_summary' => ['message' => $e->getMessage()],
                'finished_at' => \now(),
            ]);
        }
    }

    private function handleDosenSync(SyncRun $run): void
    {
        $sevima = app(SevimaApiService::class);
        $writer = new DosenSyncWriter($sevima);

        $raw = $sevima->getDosen();
        $result = $writer->sync(is_array($raw) ? $raw : []);

        foreach ($result['errors'] as $error) {
            SyncRunItem::create([
                'sync_run_id' => $run->id,
                'entity_type' => 'dosen',
                'external_id' => $error['external_id'] ?? null,
                'level' => 'error',
                'message' => $error['message'] ?? 'Unknown error',
                'payload' => $error['payload'] ?? null,
                'processed_at' => \now(),
            ]);
        }

        $run->refresh();
        $summary = is_array($run->error_summary) ? $run->error_summary : [];
        $existingErrorCount = (int) (($summary['error_count'] ?? 0));
        $summary['error_count'] = $existingErrorCount + count($result['errors'] ?? []);

        $run->update([
            'fetched_count' => ($run->fetched_count ?? 0) + ($result['fetched_count'] ?? 0),
            'processed_count' => ($run->processed_count ?? 0) + ($result['processed_count'] ?? 0),
            'inserted_count' => ($run->inserted_count ?? 0) + ($result['inserted_count'] ?? 0),
            'updated_count' => ($run->updated_count ?? 0) + ($result['updated_count'] ?? 0),
            'failed_count' => ($run->failed_count ?? 0) + ($result['failed_count'] ?? 0),
            'error_summary' => $summary,
        ]);
    }

    private function handleEmployeeSync(SyncRun $run): void
    {
        $sevima = app(SevimaApiService::class);
        $writer = new EmployeeSyncWriter($sevima);

        $raw = $sevima->getPegawai();
        $result = $writer->sync(is_array($raw) ? $raw : []);

        foreach ($result['errors'] as $error) {
            SyncRunItem::create([
                'sync_run_id' => $run->id,
                'entity_type' => 'employee',
                'external_id' => $error['external_id'] ?? null,
                'level' => 'error',
                'message' => $error['message'] ?? 'Unknown error',
                'payload' => $error['payload'] ?? null,
                'processed_at' => \now(),
            ]);
        }

        $run->refresh();
        $summary = is_array($run->error_summary) ? $run->error_summary : [];
        $existingErrorCount = (int) (($summary['error_count'] ?? 0));
        $summary['error_count'] = $existingErrorCount + count($result['errors'] ?? []);

        $run->update([
            'fetched_count' => ($run->fetched_count ?? 0) + ($result['fetched_count'] ?? 0),
            'processed_count' => ($run->processed_count ?? 0) + ($result['processed_count'] ?? 0),
            'inserted_count' => ($run->inserted_count ?? 0) + ($result['inserted_count'] ?? 0),
            'updated_count' => ($run->updated_count ?? 0) + ($result['updated_count'] ?? 0),
            'failed_count' => ($run->failed_count ?? 0) + ($result['failed_count'] ?? 0),
            'error_summary' => $summary,
        ]);
    }

    private function handleReconcile(SyncRun $run, string $mode): void
    {
        $reconcileResult = app(UserEmployeeLinkReconciler::class)->reconcile($mode);

        foreach ($reconcileResult['conflicts'] as $conflict) {
            SyncRunItem::create([
                'sync_run_id' => $run->id,
                'entity_type' => $mode,
                'external_id' => $conflict['nip'] ?? null,
                'level' => 'warning',
                'message' => 'User link reconciliation conflict',
                'payload' => $conflict,
                'processed_at' => \now(),
            ]);
        }

        $run->refresh();
        $summary = is_array($run->error_summary) ? $run->error_summary : [];
        $existingReconcile = $summary['reconcile'] ?? ['linked_count' => 0, 'conflict_count' => 0];

        $summary['reconcile'] = [
            'linked_count' => (int) ($existingReconcile['linked_count'] ?? 0) + (int) ($reconcileResult['linked_count'] ?? 0),
            'conflict_count' => (int) ($existingReconcile['conflict_count'] ?? 0) + (int) ($reconcileResult['conflict_count'] ?? 0),
        ];

        $run->update([
            'error_summary' => $summary,
        ]);
    }
}
