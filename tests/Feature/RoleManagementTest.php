<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create basic roles and permissions
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_user_can_have_multiple_roles(): void
    {
        $user = User::factory()->create();

        $adminRole = Role::findByName('admin-sdm');
        $staffRole = Role::findByName('staff');

        $user->assignRole(['admin-sdm', 'staff']);

        $this->assertTrue($user->hasRole('admin-sdm'));
        $this->assertTrue($user->hasRole('staff'));
        $this->assertCount(2, $user->roles);
    }

    public function test_user_can_switch_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole(['admin-sdm', 'staff']);

        $this->actingAs($user);

        // Set active role to admin-sdm
        $response = $this->post('/switch-role', [
            'role' => 'admin-sdm',
        ]);

        $response->assertRedirect();
        $this->assertEquals('admin-sdm', session('active_role'));
    }

    public function test_user_cannot_switch_to_role_they_dont_have(): void
    {
        $user = User::factory()->create();
        $user->assignRole('staff');

        $this->actingAs($user);

        // Try to switch to admin-sdm role (which user doesn't have)
        $response = $this->post('/switch-role', [
            'role' => 'admin-sdm',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'You do not have permission to switch to this role.');
    }

    public function test_role_helper_functions_work_correctly(): void
    {
        $user = User::factory()->create();
        $user->assignRole(['admin-sdm', 'staff']);

        $this->actingAs($user);

        // Test setActiveRole helper
        setActiveRole('admin-sdm');
        $this->assertEquals('admin-sdm', getActiveRole());

        // Test hasRole helper
        $this->assertTrue(hasRole('admin-sdm'));
        $this->assertTrue(hasRole('staff'));
        $this->assertFalse(hasRole('super-admin'));

        // Test isActiveRole helper
        $this->assertTrue(isActiveRole('admin-sdm'));
        $this->assertFalse(isActiveRole('staff'));

        // Test getUserRoles helper
        $roles = getUserRoles();
        $this->assertCount(2, $roles);
        $this->assertContains('admin-sdm', $roles->pluck('name')->toArray());

        // Test canSwitchToRole helper
        $this->assertTrue(canSwitchToRole('staff'));
        $this->assertFalse(canSwitchToRole('super-admin'));
    }

    public function test_user_defaults_to_first_role_when_no_active_role_set(): void
    {
        $user = User::factory()->create();
        $user->assignRole(['staff', 'admin-sdm']); // staff will be first alphabetically

        $this->actingAs($user);

        // When no active role is set, should default to first role
        $activeRole = getActiveRole();
        $userRoles = getUserRoles();
        $this->assertEquals($userRoles->first()->name, $activeRole);
    }
}
