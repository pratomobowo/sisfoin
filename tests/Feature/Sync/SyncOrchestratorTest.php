<?php

namespace Tests\Feature\Sync;

use App\Jobs\Sync\RunSdmSyncJob;
use App\Models\User;
use App\Services\Sync\SyncOrchestratorService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Tests\Feature\Sync\Concerns\CreatesActivityLogTable;
use Tests\TestCase;

class SyncOrchestratorTest extends TestCase
{
    use CreatesActivityLogTable;

    protected function setUp(): void
    {
        parent::setUp();

        $migrationBasePath = is_file(getcwd().'/database/migrations/0001_01_01_000000_create_users_table.php')
            ? getcwd().'/database/migrations'
            : base_path('database/migrations');

        Artisan::call('migrate:fresh', [
            '--path' => $migrationBasePath.'/0001_01_01_000000_create_users_table.php',
            '--realpath' => true,
        ]);

        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2026_02_23_000001_create_sync_runs_table.php',
            '--realpath' => true,
        ]);

        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2026_02_23_000002_create_sync_run_items_table.php',
            '--realpath' => true,
        ]);

        $this->ensureActivityLogTableExists();
    }

    public function test_start_creates_sync_run_and_dispatches_job_after_response(): void
    {
        Bus::fake();
        $user = User::create([
            'name' => 'Sync Tester',
            'email' => 'sync-tester@example.com',
            'password' => 'password',
        ]);

        $service = app(SyncOrchestratorService::class);
        $run = $service->start('employee', $user->id, 'manual-sync');

        $this->assertDatabaseHas('sync_runs', [
            'id' => $run->id,
            'mode' => 'employee',
            'status' => 'pending',
            'triggered_by' => $user->id,
        ]);

        Bus::assertDispatchedAfterResponse(RunSdmSyncJob::class, function (RunSdmSyncJob $job) use ($run) {
            return $job->syncRunId === $run->id;
        });
    }

    public function test_start_with_same_context_reuses_run_without_double_dispatch(): void
    {
        Bus::fake();
        $user = User::create([
            'name' => 'Sync Tester',
            'email' => 'sync-tester-2@example.com',
            'password' => 'password',
        ]);

        $service = app(SyncOrchestratorService::class);

        $first = $service->start('employee', $user->id, 'manual-sync');
        $second = $service->start('employee', $user->id, 'manual-sync');

        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('sync_runs', 1);
        Bus::assertDispatchedAfterResponseTimes(RunSdmSyncJob::class, 1);
    }

    public function test_start_creates_new_run_when_previous_run_is_failed(): void
    {
        Bus::fake();

        $user = User::create([
            'name' => 'Sync Tester',
            'email' => 'sync-tester-3@example.com',
            'password' => 'password',
        ]);

        $service = app(SyncOrchestratorService::class);

        $first = $service->start('employee', $user->id, 'manual-sync');
        $first->update(['status' => 'failed']);

        $second = $service->start('employee', $user->id, 'manual-sync');

        $this->assertNotSame($first->id, $second->id);
        $this->assertDatabaseCount('sync_runs', 2);
        Bus::assertDispatchedAfterResponseTimes(RunSdmSyncJob::class, 2);
    }

    public function test_start_retries_when_previous_pending_run_is_stale(): void
    {
        Bus::fake();

        $user = User::create([
            'name' => 'Sync Tester',
            'email' => 'sync-tester-4@example.com',
            'password' => 'password',
        ]);

        $service = app(SyncOrchestratorService::class);

        $stale = $service->start('employee', $user->id, 'manual-sync');
        $stale->forceFill([
            'status' => 'pending',
            'created_at' => now()->subMinutes(3),
            'updated_at' => now()->subMinutes(3),
        ])->save();

        $retried = $service->start('employee', $user->id, 'manual-sync');

        $this->assertNotSame($stale->id, $retried->id);
        $this->assertDatabaseHas('sync_runs', [
            'id' => $stale->id,
            'status' => 'failed',
        ]);
        $this->assertDatabaseHas('sync_runs', [
            'id' => $retried->id,
            'status' => 'pending',
        ]);
        Bus::assertDispatchedAfterResponseTimes(RunSdmSyncJob::class, 2);
    }

    public function test_start_keeps_recent_running_run_instead_of_retrying(): void
    {
        Bus::fake();

        $user = User::create([
            'name' => 'Sync Tester',
            'email' => 'sync-tester-5@example.com',
            'password' => 'password',
        ]);

        $service = app(SyncOrchestratorService::class);

        $running = $service->start('employee', $user->id, 'manual-sync');
        $running->forceFill([
            'status' => 'running',
            'started_at' => now()->subMinutes(5),
            'updated_at' => now()->subMinutes(1),
        ])->save();

        $sameRun = $service->start('employee', $user->id, 'manual-sync');

        $this->assertSame($running->id, $sameRun->id);
        Bus::assertDispatchedAfterResponseTimes(RunSdmSyncJob::class, 1);
    }
}
