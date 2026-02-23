<?php

namespace App\Services\Sync;

use App\Jobs\Sync\RunSdmSyncJob;
use App\Models\SyncRun;

class SyncOrchestratorService
{
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

        if ($existing) {
            return $existing;
        }

        if (! $this->lockService->acquire($normalizedMode, 30)) {
            return SyncRun::create([
                'mode' => $normalizedMode,
                'status' => 'failed',
                'triggered_by' => $triggeredBy,
                'idempotency_key' => $idempotencyKey,
                'error_summary' => ['message' => 'Sync already running for this mode'],
            ]);
        }

        try {
            $run = SyncRun::create([
                'mode' => $normalizedMode,
                'status' => 'pending',
                'triggered_by' => $triggeredBy,
                'idempotency_key' => $idempotencyKey,
            ]);

            RunSdmSyncJob::dispatch($run->id);

            return $run;
        } finally {
            $this->lockService->release($normalizedMode);
        }
    }
}
