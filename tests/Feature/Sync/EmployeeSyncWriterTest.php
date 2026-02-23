<?php

namespace Tests\Feature\Sync;

use App\Services\SevimaApiService;
use App\Services\Sync\Writers\EmployeeSyncWriter;
use Illuminate\Support\Facades\Artisan;
use Tests\Feature\Sync\Concerns\CreatesActivityLogTable;
use Tests\TestCase;

class EmployeeSyncWriterTest extends TestCase
{
    use CreatesActivityLogTable;

    protected function setUp(): void
    {
        parent::setUp();

        $migrationBasePath = is_file(getcwd().'/database/migrations/0001_01_01_000000_create_users_table.php')
            ? getcwd().'/database/migrations'
            : base_path('database/migrations');

        Artisan::call('migrate:fresh', [
            '--path' => $migrationBasePath.'/2025_09_26_170000_create_employees_table_from_sevima.php',
            '--realpath' => true,
        ]);

        $this->ensureActivityLogTableExists();
    }

    public function test_sync_upserts_employee_records_by_id_pegawai(): void
    {
        $sevimaService = app(SevimaApiService::class);
        $writer = new EmployeeSyncWriter($sevimaService);

        $firstRun = $writer->sync([
            [
                'id_pegawai' => 'E001',
                'nama' => 'Pegawai Satu',
                'nip' => '111',
                'status_aktif' => 'Aktif',
                'email' => 'e1@example.com',
            ],
            [
                'id_pegawai' => 'E002',
                'nama' => 'Pegawai Dua',
                'nip' => '222',
                'status_aktif' => 'Aktif',
                'email' => 'e2@example.com',
            ],
        ]);

        $this->assertSame(2, $firstRun['inserted_count']);
        $this->assertSame(0, $firstRun['updated_count']);
        $this->assertDatabaseCount('employees', 2);

        $secondRun = $writer->sync([
            [
                'id_pegawai' => 'E001',
                'nama' => 'Pegawai Satu Updated',
                'nip' => '111',
                'status_aktif' => 'Aktif',
                'email' => 'e1-updated@example.com',
            ],
            [
                'id_pegawai' => 'E003',
                'nama' => 'Pegawai Tiga',
                'nip' => '333',
                'status_aktif' => 'Aktif',
                'email' => 'e3@example.com',
            ],
        ]);

        $this->assertSame(1, $secondRun['inserted_count']);
        $this->assertSame(1, $secondRun['updated_count']);
        $this->assertDatabaseCount('employees', 3);
        $this->assertDatabaseHas('employees', [
            'id_pegawai' => 'E001',
            'nama' => 'Pegawai Satu Updated',
        ]);
    }

    public function test_sync_collects_error_when_id_pegawai_missing(): void
    {
        $sevimaService = app(SevimaApiService::class);
        $writer = new EmployeeSyncWriter($sevimaService);

        $result = $writer->sync([
            [
                'nama' => 'Tanpa ID',
                'nip' => '999',
            ],
        ]);

        $this->assertSame(1, $result['failed_count']);
        $this->assertCount(1, $result['errors']);
        $this->assertDatabaseCount('employees', 0);
    }
}
