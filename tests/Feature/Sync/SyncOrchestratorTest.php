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

    public function test_start_creates_sync_run_and_dispatches_job(): void
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

        Bus::assertDispatched(RunSdmSyncJob::class, function (RunSdmSyncJob $job) use ($run) {
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
        Bus::assertDispatchedTimes(RunSdmSyncJob::class, 1);
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
        Bus::assertDispatchedTimes(RunSdmSyncJob::class, 2);
    }
}
