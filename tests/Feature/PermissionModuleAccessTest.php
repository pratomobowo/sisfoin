<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionModuleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_custom_role_with_payroll_view_permission_can_access_payroll_index(): void
    {
        $role = Role::create(['name' => 'payroll-viewer', 'guard_name' => 'web']);
        $role->givePermissionTo('payroll.view');

        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user);
        setActiveRole('payroll-viewer');

        $this->get(route('sdm.slip-gaji.index'))->assertOk();
    }

    public function test_custom_role_without_payroll_view_permission_cannot_access_payroll_index(): void
    {
        $role = Role::create(['name' => 'no-payroll', 'guard_name' => 'web']);
        $role->givePermissionTo('profile.view');

        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user);
        setActiveRole('no-payroll');

        $this->get(route('sdm.slip-gaji.index'))->assertForbidden();
    }

    public function test_custom_role_without_attendance_permission_cannot_access_employee_attendance_index(): void
    {
        $role = Role::create(['name' => 'profile-only', 'guard_name' => 'web']);
        $role->givePermissionTo('profile.view');

        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user);
        setActiveRole('profile-only');

        $this->get(route('sdm.employee-attendance.index'))->assertForbidden();
    }

    public function test_custom_role_without_attendance_permission_cannot_access_fingerprint_index(): void
    {
        $role = Role::create(['name' => 'fingerprint-denied', 'guard_name' => 'web']);
        $role->givePermissionTo('profile.view');

        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user);
        setActiveRole('fingerprint-denied');

        $this->get(route('sdm.fingerprint.index'))->assertForbidden();
    }

    public function test_custom_role_with_surat_keputusan_view_permission_can_access_surat_keputusan_index(): void
    {
        $role = Role::create(['name' => 'sk-viewer', 'guard_name' => 'web']);
        $role->givePermissionTo('surat_keputusan.view');

        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user);
        setActiveRole('sk-viewer');

        $this->get(route('sekretariat.surat-keputusan.index'))->assertOk();
    }

    public function test_sidebar_shows_modules_from_all_assigned_role_permissions(): void
    {
        $sdmRole = Role::create(['name' => 'custom-sdm-menu', 'guard_name' => 'web']);
        $sdmRole->givePermissionTo('employees.view');

        $sekretariatRole = Role::create(['name' => 'custom-sekretariat-menu', 'guard_name' => 'web']);
        $sekretariatRole->givePermissionTo('sekretariat.view', 'surat_keputusan.view');

        $user = User::factory()->create();
        $user->assignRole($sdmRole, $sekretariatRole);

        $this->actingAs($user);
        setActiveRole('custom-sdm-menu');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('SDM')
            ->assertSee('Data Karyawan')
            ->assertSee('Sekretariat')
            ->assertSee('Manajemen Pengumuman')
            ->assertSee('Surat Keputusan');
    }

    public function test_sidebar_hides_modules_without_permission(): void
    {
        $role = Role::create(['name' => 'custom-profile-menu', 'guard_name' => 'web']);
        $role->givePermissionTo('profile.view');

        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user);
        setActiveRole('custom-profile-menu');

        $this->get(route('dashboard'))->assertForbidden();

        $role->givePermissionTo('employees.view');
        $user->forgetCachedPermissions();

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('SDM')
            ->assertSee('Data Karyawan')
            ->assertDontSee('Manajemen Pengumuman')
            ->assertDontSee('Surat Keputusan');
    }

    public function test_sidebar_shows_staff_menu_when_user_has_staff_role_even_if_active_role_is_admin(): void
    {
        $sdmRole = Role::create(['name' => 'custom-sdm-with-staff', 'guard_name' => 'web']);
        $sdmRole->givePermissionTo('employees.view');

        $user = User::factory()->create();
        $user->assignRole('staff', $sdmRole);

        $this->actingAs($user);
        setActiveRole('custom-sdm-with-staff');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('SDM')
            ->assertSee('Layanan Mandiri')
            ->assertSee('Informasi Gaji');
    }
}
