<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Superadmin\RoleManagement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
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

    public function test_superadmin_can_view_role_management_component(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        Livewire::test(RoleManagement::class)
            ->assertStatus(200)
            ->assertSee('Manajemen Peran')
            ->assertSee('Tambah Peran');
    }

    public function test_role_management_displays_roles(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        $component = Livewire::test(RoleManagement::class);

        // Should see default roles (checking the actual role names)
        $component->assertSee('super-admin')
            ->assertSee('admin-sdm')
            ->assertSee('staff');
    }

    public function test_superadmin_can_create_new_role(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        $permissions = Permission::limit(2)->pluck('name')->toArray();

        Livewire::test(RoleManagement::class)
            ->set('name', 'admin-test')
            ->set('display_name', 'Admin Test')
            ->set('description', 'Role for testing purposes')
            ->set('selectedPermissions', $permissions)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('roles', [
            'name' => 'admin-test',
            'description' => 'Role for testing purposes',
        ]);

        $role = Role::where('name', 'admin-test')->first();
        $this->assertTrue($role->hasAnyPermission($permissions));
    }

    public function test_superadmin_can_edit_role(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        // Create a test role
        $role = Role::create([
            'name' => 'test-role',
            'description' => 'Original description',
        ]);

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        $permissions = Permission::limit(1)->pluck('name')->toArray();

        Livewire::test(RoleManagement::class)
            ->call('edit', $role->id)
            ->set('name', 'updated-role')
            ->set('description', 'Updated description')
            ->set('selectedPermissions', $permissions)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'updated-role',
            'description' => 'Updated description',
        ]);
    }

    public function test_cannot_edit_super_admin_role(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        $superAdminRole = Role::where('name', 'super-admin')->first();

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        Livewire::test(RoleManagement::class)
            ->call('edit', $superAdminRole->id)
            ->assertSet('showModal', false);
    }

    public function test_superadmin_can_delete_role(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        // Create a test role without users
        $role = Role::create([
            'name' => 'deletable-role',
            'description' => 'Can be deleted',
        ]);

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        Livewire::test(RoleManagement::class)
            ->call('confirmDelete', $role->id)
            ->assertSet('showDeleteModal', true)
            ->call('delete');

        $this->assertDatabaseMissing('roles', [
            'id' => $role->id,
        ]);
    }

    public function test_cannot_delete_role_with_users(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        // Create a role and assign it to a user
        $role = Role::create(['name' => 'role-with-users']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        Livewire::test(RoleManagement::class)
            ->call('confirmDelete', $role->id)
            ->assertSet('showDeleteModal', false);

        // Role should still exist
        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
        ]);
    }

    public function test_cannot_delete_super_admin_role(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        $superAdminRole = Role::where('name', 'super-admin')->first();

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        Livewire::test(RoleManagement::class)
            ->call('confirmDelete', $superAdminRole->id)
            ->assertSet('showDeleteModal', false);

        // Super-admin role should still exist
        $this->assertDatabaseHas('roles', [
            'name' => 'super-admin',
        ]);
    }

    public function test_role_validation_works(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        // Test empty name
        Livewire::test(RoleManagement::class)
            ->set('name', '')
            ->set('display_name', 'Test Role')
            ->call('save')
            ->assertHasErrors(['name']);

        // Test invalid name format
        Livewire::test(RoleManagement::class)
            ->set('name', 'Invalid Name With Spaces')
            ->set('display_name', 'Test Role')
            ->call('save')
            ->assertHasErrors(['name']);

        // Test duplicate name
        Livewire::test(RoleManagement::class)
            ->set('name', 'staff') // Already exists
            ->set('display_name', 'Test Role')
            ->call('save')
            ->assertHasErrors(['name']);
    }

    public function test_search_functionality_works(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        // Create test roles
        Role::create(['name' => 'searchable-role']);
        Role::create(['name' => 'another-role']);

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        Livewire::test(RoleManagement::class)
            ->set('search', 'searchable')
            ->assertSee('searchable-role')
            ->assertDontSee('another-role');
    }

    public function test_permissions_are_grouped_correctly(): void
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('super-admin');

        $this->actingAs($superadmin);
        setActiveRole('super-admin');

        $component = Livewire::test(RoleManagement::class)
            ->call('create');

        // Check that permissions are grouped by category
        $permissions = $component->viewData('permissions');
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $permissions);

        // Convert to array for easier assertion
        $permissionsArray = $permissions->toArray();

        // Should have permission groups like 'users', 'roles', 'superadmin'
        $this->assertArrayHasKey('users', $permissionsArray);
        $this->assertArrayHasKey('roles', $permissionsArray);
        $this->assertArrayHasKey('superadmin', $permissionsArray);
    }
}
