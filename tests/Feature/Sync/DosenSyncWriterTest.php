<?php

namespace Tests\Feature\Sync;

use App\Services\SevimaApiService;
use App\Services\Sync\Writers\DosenSyncWriter;
use Illuminate\Support\Facades\Artisan;
use Tests\Feature\Sync\Concerns\CreatesActivityLogTable;
use Tests\TestCase;

class DosenSyncWriterTest extends TestCase
{
    use CreatesActivityLogTable;

    protected function setUp(): void
    {
        parent::setUp();

        $migrationBasePath = is_file(getcwd().'/database/migrations/0001_01_01_000000_create_users_table.php')
            ? getcwd().'/database/migrations'
            : base_path('database/migrations');

        Artisan::call('migrate:fresh', [
            '--path' => $migrationBasePath.'/2025_09_13_223620_create_dosens_table.php',
            '--realpath' => true,
        ]);

        $this->ensureActivityLogTableExists();
    }

    public function test_sync_upserts_dosen_records_by_id_pegawai(): void
    {
        $sevimaService = app(SevimaApiService::class);
        $writer = new DosenSyncWriter($sevimaService);

        $firstRun = $writer->sync([
            [
                'id_pegawai' => 'D001',
                'nama' => 'Dosen Satu',
                'nip' => '111',
                'status_aktif' => 'AA',
                'email' => 'd1@example.com',
            ],
            [
                'id_pegawai' => 'D002',
                'nama' => 'Dosen Dua',
                'nip' => '222',
                'status_aktif' => 'AA',
                'email' => 'd2@example.com',
            ],
        ]);

        $this->assertSame(2, $firstRun['inserted_count']);
        $this->assertSame(0, $firstRun['updated_count']);
        $this->assertDatabaseCount('dosens', 2);

        $secondRun = $writer->sync([
            [
                'id_pegawai' => 'D001',
                'nama' => 'Dosen Satu Updated',
                'nip' => '111',
                'status_aktif' => 'AA',
                'email' => 'd1-updated@example.com',
            ],
            [
                'id_pegawai' => 'D003',
                'nama' => 'Dosen Tiga',
                'nip' => '333',
                'status_aktif' => 'AA',
                'email' => 'd3@example.com',
            ],
        ]);

        $this->assertSame(1, $secondRun['inserted_count']);
        $this->assertSame(1, $secondRun['updated_count']);
        $this->assertDatabaseCount('dosens', 3);
        $this->assertDatabaseHas('dosens', [
            'id_pegawai' => 'D001',
            'nama' => 'Dosen Satu Updated',
        ]);
    }

    public function test_sync_collects_error_when_id_pegawai_missing(): void
    {
        $sevimaService = app(SevimaApiService::class);
        $writer = new DosenSyncWriter($sevimaService);

        $result = $writer->sync([
            [
                'nama' => 'Tanpa ID',
                'nip' => '999',
            ],
        ]);

        $this->assertSame(1, $result['failed_count']);
        $this->assertCount(1, $result['errors']);
        $this->assertDatabaseCount('dosens', 0);
    }
}
