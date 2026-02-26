<?php

namespace Tests\Feature;

use App\Models\SlipGajiDetail;
use App\Models\SlipGajiHeader;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Sync\Concerns\CreatesActivityLogTable;
use Tests\TestCase;

class SlipGajiDetailResolutionTest extends TestCase
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
            '--path' => $migrationBasePath.'/2025_09_26_170000_create_employees_table_from_sevima.php',
            '--realpath' => true,
        ]);
        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2025_09_13_223620_create_dosens_table.php',
            '--realpath' => true,
        ]);
        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2025_09_14_153840_create_slip_gaji_header_table.php',
            '--realpath' => true,
        ]);
        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2026_01_05_105226_add_mode_to_slip_gaji_header_table.php',
            '--realpath' => true,
        ]);
        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2026_01_28_155210_update_unique_constraint_on_slip_gaji_header_table.php',
            '--realpath' => true,
        ]);
        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2025_09_14_153845_create_slip_gaji_detail_table.php',
            '--realpath' => true,
        ]);
        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2025_09_15_161048_remove_nama_column_from_slip_gaji_detail_table.php',
            '--realpath' => true,
        ]);
        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2025_09_15_194359_update_slip_gaji_detail_table_structure.php',
            '--realpath' => true,
        ]);
        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2025_09_15_100041_rename_raihan_s2s3_to_raihan_pendidikan_in_slip_gaji_detail.php',
            '--realpath' => true,
        ]);
        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2025_09_15_195741_reorder_slip_gaji_detail_fields.php',
            '--realpath' => true,
        ]);
        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2025_10_02_000000_drop_upz_and_infaq_masjid_from_slip_gaji_detail.php',
            '--realpath' => true,
        ]);
        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2026_01_28_151620_add_dosen_perjanjian_khusus_to_slip_gaji_detail_status.php',
            '--realpath' => true,
        ]);
        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2026_01_28_153840_rename_dosen_perjanjian_khusus_to_dosen_pk_in_slip_gaji_detail.php',
            '--realpath' => true,
        ]);

        $this->ensureActivityLogTableExists();
    }

    public function test_resolves_dosen_using_nip_pns_fallback_when_nip_not_found(): void
    {
        $user = User::query()->create([
            'name' => 'Uploader',
            'email' => 'uploader@example.com',
            'password' => bcrypt('password'),
        ]);

        $header = SlipGajiHeader::query()->create([
            'periode' => '2026-02',
            'mode' => 'standard',
            'file_original' => 'test.xlsx',
            'uploaded_by' => $user->id,
            'uploaded_at' => now(),
        ]);

        DB::table('dosens')->insert([
            'id_pegawai' => 'D001',
            'nip' => '0011067301',
            'nip_pns' => '197107212005011008',
            'nama' => 'Dosen DPK',
            'gelar_depan' => 'Dr.',
            'gelar_belakang' => 'M.T.',
            'email_kampus' => 'dpk@kampus.test',
            'status_aktif' => 'Aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'header_id' => $header->id,
            'status' => 'DOSEN_DPK',
            'nip' => '197107212005011008',
            'gaji_pokok' => 1000000,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('slip_gaji_detail', 'penerimaan_bersih')) {
            $payload['penerimaan_bersih'] = 800000;
        }

        if (Schema::hasColumn('slip_gaji_detail', 'gaji_bersih')) {
            $payload['gaji_bersih'] = 800000;
        }

        DB::table('slip_gaji_detail')->insert($payload);

        $detail = SlipGajiDetail::query()->where('header_id', $header->id)->where('nip', '197107212005011008')->firstOrFail();

        $resolved = SlipGajiDetail::query()
            ->with(['employee', 'dosen', 'employeeByNipPns', 'dosenByNipPns'])
            ->findOrFail($detail->id);

        $this->assertNull($resolved->dosen);
        $this->assertNotNull($resolved->resolved_dosen);
        $this->assertSame('Dosen DPK', $resolved->resolved_dosen->nama);
        $this->assertSame('Dr. Dosen DPK, M.T.', $resolved->resolved_nama);
        $this->assertSame('dpk@kampus.test', $resolved->resolved_email);
        $this->assertSame('kampus', $resolved->resolved_email_source);
    }
}
