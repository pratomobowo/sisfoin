<?php

namespace App\Services\Sync;

use App\Jobs\Sync\RunSdmSyncJob;
use App\Models\SyncRun;

class SyncOrchestratorService
{
    private const STALE_PENDING_MINUTES = 2;

    private const STALE_RUNNING_MINUTES = 30;

    public function __construct(
        private readonly SyncLockService $lockService,
        private readonly SyncIdempotencyService $idempotencyService,
    ) {}

    public function start(string $mode, int|string|null $triggeredBy = null, string $source = 'manual'): SyncRun
    {
        $normalizedMode = strtolower($mode);
        $idempotencyKey = $this->idempotencyService->generate($normalizedMode, $triggeredBy, $source);

        $existing = SyncRun::query()
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existing && in_array($existing->status, ['pending', 'running'], true)) {
            if ($this->isStale($existing)) {
                $existing->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                    'error_summary' => [
                        'message' => 'Previous sync run expired before processing completed.',
                    ],
                ]);
            } else {
            return $existing;
            }
        }

        $effectiveIdempotencyKey = $idempotencyKey;
        if ($existing) {
            $effectiveIdempotencyKey = $idempotencyKey.'-retry-'.\now()->format('YmdHisv');
        }

        $run = SyncRun::create([
            'mode' => $normalizedMode,
            'status' => 'pending',
            'triggered_by' => $triggeredBy,
            'idempotency_key' => $effectiveIdempotencyKey,
        ]);

        RunSdmSyncJob::dispatchAfterResponse($run->id);

        return $run;
    }

    private function isStale(SyncRun $run): bool
    {
        if ($run->status === 'pending') {
            return $run->created_at !== null
                && $run->created_at->lte(now()->subMinutes(self::STALE_PENDING_MINUTES));
        }

        if ($run->status === 'running') {
            $referenceTime = $run->started_at ?? $run->updated_at ?? $run->created_at;

            return $referenceTime !== null
                && $referenceTime->lte(now()->subMinutes(self::STALE_RUNNING_MINUTES));
        }

        return false;
    }
}
