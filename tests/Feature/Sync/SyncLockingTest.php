<?php

namespace Tests\Feature\Sync;

use App\Services\Sync\SyncIdempotencyService;
use App\Services\Sync\SyncLockService;
use Tests\TestCase;

class SyncLockingTest extends TestCase
{
    public function test_lock_service_blocks_second_acquisition_for_same_mode(): void
    {
        $service = app(SyncLockService::class);

        $first = $service->acquire('employee', 10);
        $second = $service->acquire('employee', 10);

        $this->assertTrue($first);
        $this->assertFalse($second);

        $service->release('employee');
    }

    public function test_idempotency_service_returns_same_key_for_same_context(): void
    {
        $service = app(SyncIdempotencyService::class);

        $first = $service->generate('employee', 99, 'manual-sync');
        $second = $service->generate('employee', 99, 'manual-sync');

        $this->assertSame($first, $second);
    }

    public function test_idempotency_service_returns_different_key_for_different_context(): void
    {
        $service = app(SyncIdempotencyService::class);

        $first = $service->generate('employee', 99, 'manual-sync');
        $second = $service->generate('dosen', 99, 'manual-sync');

        $this->assertNotSame($first, $second);
    }
}
