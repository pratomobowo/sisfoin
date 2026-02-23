<?php

namespace App\Services\Sync;

use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;

class SyncLockService
{
    /**
     * @var array<string, Lock>
     */
    private array $locks = [];

    public function acquire(string $mode, int $seconds = 600): bool
    {
        $key = $this->lockKey($mode);
        $lock = Cache::lock($key, $seconds);

        if (! $lock->get()) {
            return false;
        }

        $this->locks[$key] = $lock;

        return true;
    }

    public function release(string $mode): void
    {
        $key = $this->lockKey($mode);

        if (! isset($this->locks[$key])) {
            Cache::lock($key)->forceRelease();

            return;
        }

        $this->locks[$key]->release();
        unset($this->locks[$key]);
    }

    private function lockKey(string $mode): string
    {
        return 'sync:mode:'.strtolower($mode);
    }
}
