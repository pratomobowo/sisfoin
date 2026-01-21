<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuperadminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create basic roles and permissions
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_superadmin_can_access_superadmin_routes(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        $response = $this->get('/superadmin');
        $response->assertStatus(200);

        $response = $this->get('/superadmin/users');
        $response->assertStatus(200);
    }

    public function test_non_superadmin_cannot_access_superadmin_routes(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin-sdm');

        $this->actingAs($user);
        setActiveRole('admin-sdm');

        $response = $this->get('/superadmin');
        $response->assertStatus(403);

        $response = $this->get('/superadmin/users');
        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_superadmin_routes(): void
    {
        $response = $this->get('/superadmin');
        $response->assertRedirect('/login');

        $response = $this->get('/superadmin/users');
        $response->assertRedirect('/login');
    }

    public function test_superadmin_can_view_dashboard(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        $response = $this->get('/superadmin');

        $response->assertStatus(200);
        $response->assertSee('Selamat datang kembali'); // Welcome back in Indonesian
        $response->assertSee('Total Pengguna'); // Total Users in Indonesian
        $response->assertSee('Total Peran'); // Total Roles in Indonesian
    }

    public function test_superadmin_can_access_user_management(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        // Create some test users
        User::factory()->count(3)->create();

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        $response = $this->get('/superadmin/users');

        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengguna'); // User Management in Indonesian
        $response->assertSee('Tambah Pengguna'); // Add User in Indonesian
    }

    public function test_superadmin_bypasses_all_permissions(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        // Test that superadmin can access even without specific permissions
        $this->assertTrue($superadmin->can('manage-users'));
        $this->assertTrue($superadmin->can('manage-roles'));
        $this->assertTrue($superadmin->can('non-existent-permission'));
    }

    public function test_user_with_superadmin_role_but_different_active_role_still_has_superadmin_access(): void
    {
        $user = User::factory()->create();
        $user->assignRole(['super-admin', 'admin-sdm']);

        $this->actingAs($user);
        setActiveRole('admin-sdm'); // Set active role to admin-sdm

        // Should still be able to access superadmin routes because user has super-admin role
        $response = $this->get('/superadmin');
        $response->assertStatus(200);
    }

    public function test_middleware_role_check_works_correctly(): void
    {
        // Test with superadmin
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        $response = $this->get('/superadmin');
        $response->assertStatus(200);

        // Test with regular admin
        $admin = User::factory()->create();
        $admin->assignRole('admin-sdm');

        $this->actingAs($admin);
        setActiveRole('admin-sdm');

        $response = $this->get('/superadmin');
        $response->assertStatus(403);

        // Test with staff
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $this->actingAs($staff);
        setActiveRole('staff');

        $response = $this->get('/superadmin');
        $response->assertStatus(403);
    }
}
