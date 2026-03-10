<?php

namespace Tests\Feature\Sync;

use App\Models\Dosen;
use App\Services\SevimaApiService;
use App\Services\Sync\Writers\DosenSyncWriter;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
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
            '--path' => $migrationBasePath.'/0001_01_01_000000_create_users_table.php',
            '--realpath' => true,
        ]);

        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2025_09_13_230642_add_nip_to_users_table.php',
            '--realpath' => true,
        ]);

        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2026_03_04_114343_add_is_active_to_users_table.php',
            '--realpath' => true,
        ]);

        Artisan::call('migrate', [
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

    public function test_sync_restores_soft_deleted_dosen_without_creating_duplicate_active_rows(): void
    {
        Log::spy();

        $dosen = Dosen::create([
            'id_pegawai' => 'D001',
            'nip' => '111',
            'nama' => 'Dosen Lama',
            'status_aktif' => 'AA',
        ]);
        $dosen->delete();

        $sevimaService = app(SevimaApiService::class);
        $writer = new DosenSyncWriter($sevimaService);

        $result = $writer->sync([
            [
                'id_pegawai' => 'D001',
                'nama' => 'Dosen Baru',
                'nip' => '111',
                'status_aktif' => 'AA',
                'email' => 'dosen@example.com',
            ],
        ]);

        $this->assertSame(0, $result['inserted_count']);
        $this->assertSame(1, $result['updated_count']);
        $this->assertSame(0, $result['failed_count']);
        $this->assertDatabaseCount('dosens', 1);

        $restored = Dosen::withTrashed()->firstOrFail();
        $this->assertFalse($restored->trashed());
        $this->assertSame('Dosen Baru', $restored->nama);

        Log::shouldHaveReceived('info')->once();
    }

    public function test_sync_skips_duplicate_active_dosen_nip_for_different_id_pegawai(): void
    {
        Dosen::create([
            'id_pegawai' => 'D001',
            'nip' => '111',
            'nama' => 'Dosen Pertama',
            'status_aktif' => 'AA',
        ]);

        $sevimaService = app(SevimaApiService::class);
        $writer = new DosenSyncWriter($sevimaService);

        $result = $writer->sync([
            [
                'id_pegawai' => 'D002',
                'nama' => 'Dosen Duplikat',
                'nip' => '111',
                'status_aktif' => 'AA',
            ],
        ]);

        $this->assertSame(0, $result['processed_count']);
        $this->assertSame(0, $result['inserted_count']);
        $this->assertSame(0, $result['updated_count']);
        $this->assertSame(1, $result['failed_count']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('Duplicate active dosen nip', $result['errors'][0]['message']);
        $this->assertDatabaseCount('dosens', 1);
        $this->assertDatabaseMissing('dosens', [
            'id_pegawai' => 'D002',
        ]);
    }
}
