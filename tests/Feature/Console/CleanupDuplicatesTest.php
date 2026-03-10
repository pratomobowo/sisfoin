<?php

namespace Tests\Feature\Console;

use App\Models\Dosen;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\Feature\Sync\Concerns\CreatesActivityLogTable;
use Tests\TestCase;

class CleanupDuplicatesTest extends TestCase
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
            '--path' => $migrationBasePath.'/2025_09_26_170000_create_employees_table_from_sevima.php',
            '--realpath' => true,
        ]);

        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2025_09_13_223620_create_dosens_table.php',
            '--realpath' => true,
        ]);

        Schema::create('slip_gaji_detail', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 50);
            $table->timestamps();
        });

        $this->ensureActivityLogTableExists();
    }

    public function test_cleanup_duplicates_merges_employee_rows_without_assuming_slip_gaji_detail_employee_id_column(): void
    {
        $older = Employee::create([
            'id_pegawai' => 'E001',
            'nip' => 'EMP-001',
            'nama' => 'Pegawai Lama',
            'status_aktif' => 'Aktif',
        ]);

        $newer = Employee::create([
            'id_pegawai' => 'E001',
            'nip' => 'EMP-001',
            'nama' => 'Pegawai Baru',
            'status_aktif' => 'Aktif',
        ]);

        User::create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => 'password',
            'nip' => 'EMP-001',
            'employee_type' => 'employee',
            'employee_id' => (string) $older->id,
        ]);

        DB::table('slip_gaji_detail')->insert([
            'nip' => 'EMP-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $exitCode = Artisan::call('cleanup:duplicates', [
            '--model' => 'employee',
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertDatabaseCount('employees', 1);
        $this->assertDatabaseHas('employees', [
            'id' => $newer->id,
            'id_pegawai' => 'E001',
            'nama' => 'Pegawai Baru',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'employee@example.com',
            'employee_type' => 'employee',
            'employee_id' => (string) $newer->id,
        ]);
        $this->assertDatabaseCount('slip_gaji_detail', 1);
    }

    public function test_cleanup_duplicates_merges_dosen_rows_without_assuming_slip_gaji_detail_dosen_id_column(): void
    {
        $older = Dosen::create([
            'id_pegawai' => 'D001',
            'nip' => 'DSN-001',
            'nama' => 'Dosen Lama',
            'status_aktif' => 'AA',
        ]);

        $newer = Dosen::create([
            'id_pegawai' => 'D001',
            'nip' => 'DSN-001',
            'nama' => 'Dosen Baru',
            'status_aktif' => 'AA',
        ]);

        User::create([
            'name' => 'Dosen User',
            'email' => 'dosen@example.com',
            'password' => 'password',
            'nip' => 'DSN-001',
            'employee_type' => 'dosen',
            'employee_id' => (string) $older->id,
        ]);

        DB::table('slip_gaji_detail')->insert([
            'nip' => 'DSN-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $exitCode = Artisan::call('cleanup:duplicates', [
            '--model' => 'dosen',
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertDatabaseCount('dosens', 1);
        $this->assertDatabaseHas('dosens', [
            'id' => $newer->id,
            'id_pegawai' => 'D001',
            'nama' => 'Dosen Baru',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'dosen@example.com',
            'employee_type' => 'dosen',
            'employee_id' => (string) $newer->id,
        ]);
        $this->assertDatabaseCount('slip_gaji_detail', 1);
    }
}
