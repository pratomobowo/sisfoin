<?php

namespace Tests\Feature\Sync;

use App\Models\Dosen;
use App\Models\Employee;
use App\Models\User;
use App\Services\Sync\Reconciler\UserEmployeeLinkReconciler;
use Illuminate\Support\Facades\Artisan;
use Tests\Feature\Sync\Concerns\CreatesActivityLogTable;
use Tests\TestCase;

class UserEmployeeLinkReconcilerTest extends TestCase
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
            '--path' => $migrationBasePath.'/2025_09_13_223620_create_dosens_table.php',
            '--realpath' => true,
        ]);

        Artisan::call('migrate', [
            '--path' => $migrationBasePath.'/2025_09_26_170000_create_employees_table_from_sevima.php',
            '--realpath' => true,
        ]);

        $this->ensureActivityLogTableExists();
    }

    public function test_reconcile_employee_updates_user_employee_id_by_nip(): void
    {
        $employee = Employee::create([
            'id_pegawai' => 'E001',
            'nip' => '12345',
            'nama' => 'Emp One',
            'status_aktif' => 'Aktif',
        ]);

        $user = User::create([
            'name' => 'Emp User',
            'email' => 'emp-user@example.com',
            'password' => 'password',
            'nip' => '12345',
            'employee_type' => 'employee',
            'employee_id' => null,
        ]);

        $reconciler = app(UserEmployeeLinkReconciler::class);
        $result = $reconciler->reconcile('employee');

        $this->assertSame(1, $result['linked_count']);
        $this->assertSame(0, $result['conflict_count']);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'employee_type' => 'employee',
            'employee_id' => (string) $employee->id,
        ]);
    }

    public function test_reconcile_dosen_updates_user_employee_id_by_nip(): void
    {
        $dosen = Dosen::create([
            'id_pegawai' => 'D001',
            'nip' => '67890',
            'nama' => 'Dosen One',
            'status_aktif' => 'AA',
        ]);

        $user = User::create([
            'name' => 'Dosen User',
            'email' => 'dosen-user@example.com',
            'password' => 'password',
            'nip' => '67890',
            'employee_type' => 'dosen',
            'employee_id' => null,
        ]);

        $reconciler = app(UserEmployeeLinkReconciler::class);
        $result = $reconciler->reconcile('dosen');

        $this->assertSame(1, $result['linked_count']);
        $this->assertSame(0, $result['conflict_count']);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'employee_type' => 'dosen',
            'employee_id' => (string) $dosen->id,
        ]);
    }

    public function test_reconcile_reports_conflict_when_multiple_records_match_same_nip(): void
    {
        Employee::create([
            'id_pegawai' => 'E001',
            'nip' => '99999',
            'nama' => 'Emp A',
            'status_aktif' => 'Aktif',
        ]);

        Employee::create([
            'id_pegawai' => 'E002',
            'nip' => '99999',
            'nama' => 'Emp B',
            'status_aktif' => 'Aktif',
        ]);

        $user = User::create([
            'name' => 'Conflict User',
            'email' => 'conflict-user@example.com',
            'password' => 'password',
            'nip' => '99999',
            'employee_type' => 'employee',
            'employee_id' => null,
        ]);

        $reconciler = app(UserEmployeeLinkReconciler::class);
        $result = $reconciler->reconcile('employee');

        $this->assertSame(0, $result['linked_count']);
        $this->assertSame(1, $result['conflict_count']);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'employee_id' => null,
        ]);
    }
}
