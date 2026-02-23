<?php

namespace Tests\Feature\Sync;

use App\Models\SyncRun;
use App\Models\SyncRunItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SyncRunSchemaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $migrationBasePath = is_file(getcwd().'/database/migrations/0001_01_01_000000_create_users_table.php')
            ? getcwd().'/database/migrations'
            : base_path('database/migrations');

        $usersMigration = $migrationBasePath.'/0001_01_01_000000_create_users_table.php';
        $syncRunsMigration = $migrationBasePath.'/2026_02_23_000001_create_sync_runs_table.php';
        $syncRunItemsMigration = $migrationBasePath.'/2026_02_23_000002_create_sync_run_items_table.php';

        Artisan::call('migrate:fresh', [
            '--path' => $usersMigration,
            '--realpath' => true,
        ]);

        Artisan::call('migrate', [
            '--path' => $syncRunsMigration,
            '--realpath' => true,
        ]);

        Artisan::call('migrate', [
            '--path' => $syncRunItemsMigration,
            '--realpath' => true,
        ]);
    }

    public function test_sync_runs_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('sync_runs'));

        $expectedColumns = [
            'id',
            'mode',
            'status',
            'triggered_by',
            'idempotency_key',
            'fetched_count',
            'processed_count',
            'inserted_count',
            'updated_count',
            'skipped_count',
            'failed_count',
            'error_summary',
            'started_at',
            'finished_at',
            'created_at',
            'updated_at',
        ];

        foreach ($expectedColumns as $column) {
            $this->assertTrue(Schema::hasColumn('sync_runs', $column), "Missing column: {$column}");
        }
    }

    public function test_sync_run_items_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('sync_run_items'));

        $expectedColumns = [
            'id',
            'sync_run_id',
            'entity_type',
            'external_id',
            'level',
            'message',
            'payload',
            'processed_at',
            'created_at',
            'updated_at',
        ];

        foreach ($expectedColumns as $column) {
            $this->assertTrue(Schema::hasColumn('sync_run_items', $column), "Missing column: {$column}");
        }
    }

    public function test_sync_run_and_item_models_define_relationships(): void
    {
        $syncRun = new SyncRun;
        $syncRunItem = new SyncRunItem;

        $this->assertInstanceOf(HasMany::class, $syncRun->items());
        $this->assertInstanceOf(BelongsTo::class, $syncRunItem->syncRun());
    }
}
