<?php

namespace Tests\Feature\Livewire;

use App\Models\User;
use App\Models\Role;
use App\Livewire\Superadmin\UserManagement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user for testing
        $this->adminUser = User::factory()->create();
        $adminRole = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole($adminRole);
    }

    /** @test */
    public function user_management_component_renders_correctly()
    {
        Livewire::actingAs($this->adminUser)
            ->test(UserManagement::class)
            ->assertStatus(200)
            ->assertViewHas('users')
            ->assertViewHas('roles');
    }

    /** @test */
    public function it_displays_users_list()
    {
        // Create test users
        User::factory()->count(5)->create();

        Livewire::actingAs($this->adminUser)
            ->test(UserManagement::class)
            ->assertSee('Daftar Pengguna')
            ->assertViewHas('users', function ($users) {
                return $users->count() >= 5; // At least 5 users plus admin user
            });
    }

    /** @test */
    public function it_can_filter_users_by_search()
    {
        $user1 = User::factory()->create(['name' => 'John Doe Test']);
        $user2 = User::factory()->create(['name' => 'Jane Smith']);

        Livewire::actingAs($this->adminUser)
            ->test(UserManagement::class)
            ->set('search', 'John Doe')
            ->assertSee('John Doe Test')
            ->assertDontSee('Jane Smith');
    }

    /** @test */
    public function it_can_filter_users_by_role()
    {
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $userRole = Role::create(['name' => 'user', 'guard_name' => 'web']);

        $adminUser = User::factory()->create();
        $regularUser = User::factory()->create();

        $adminUser->assignRole($adminRole);
        $regularUser->assignRole($userRole);

        Livewire::actingAs($this->adminUser)
            ->test(UserManagement::class)
            ->set('selectedRole', $adminRole->id)
            ->assertSee($adminUser->name)
            ->assertDontSee($regularUser->name);
    }

    /** @test */
    public function it_can_change_per_page_option()
    {
        // Create many users
        User::factory()->count(25)->create();

        Livewire::actingAs($this->adminUser)
            ->test(UserManagement::class)
            ->set('perPage', 25)
            ->assertViewHas('users', function ($users) {
                return $users->perPage() === 25;
            });
    }

    /** @test */
    public function it_can_reset_filters()
    {
        Livewire::actingAs($this->adminUser)
            ->test(UserManagement::class)
            ->set('search', 'test search')
            ->set('selectedRole', 1)
            ->set('perPage', 50)
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('selectedRole', '')
            ->assertSet('perPage', 10);
    }

    /** @test */
    public function it_can_open_create_user_modal()
    {
        Livewire::actingAs($this->adminUser)
            ->test(UserManagement::class)
            ->call('openCreateModal')
            ->assertDispatched('openUserCreateForm');
    }

    /** @test */
    public function it_can_open_edit_user_modal()
    {
        $user = User::factory()->create();

        Livewire::actingAs($this->adminUser)
            ->test(UserManagement::class)
            ->call('openEditModal', $user->id)
            ->assertDispatched('openUserForm', $user->id);
    }

    /** @test */
    public function it_can_show_delete_confirmation_modal()
    {
        $user = User::factory()->create();

        Livewire::actingAs($this->adminUser)
            ->test(UserManagement::class)
            ->call('confirmDelete', $user->id)
            ->assertSet('showDeleteModal', true)
            ->assertSet('userToDelete.id', $user->id);
    }

    /** @test */
    public function it_can_close_delete_confirmation_modal()
    {
        $user = User::factory()->create();

        Livewire::actingAs($this->adminUser)
            ->test(UserManagement::class)
            ->call('confirmDelete', $user->id)
            ->call('closeDeleteModal')
            ->assertSet('showDeleteModal', false)
            ->assertSet('userToDelete', null);
    }

    /** @test */
    public function it_can_delete_user()
    {
        $user = User::factory()->create();

        Livewire::actingAs($this->adminUser)
            ->test(UserManagement::class)
            ->call('confirmDelete', $user->id)
            ->call('delete')
            ->assertSet('showDeleteModal', false)
            ->assertSet('userToDelete', null)
            ->assertDispatched('userDeleted')
            ->assertSessionHas('success', 'Pengguna berhasil dihapus');

        $this->assertNull(User::find($user->id));
    }

    /** @test */
    public function it_cannot_delete_own_account()
    {
        Livewire::actingAs($this->adminUser)
            ->test(UserManagement::class)
            ->call('confirmDelete', $this->adminUser->id)
            ->call('delete')
            ->assertSessionHas('error', 'Gagal menghapus pengguna: Cannot delete own account');

        // Verify admin user still exists
        $this->assertNotNull(User::find($this->adminUser->id));
    }

    /** @test */
    public function it_can_show_import_users_modal()
    {
        Livewire::actingAs($this->adminUser)
            ->test(UserManagement::class)
            ->call('showImportUsers')
            ->assertDispatched('showUserImport');
    }

    /** @test */
    public function pagination_works_correctly()
    {
        // Create many users for pagination testing
        User::factory()->count(25)->create();

        Livewire::actingAs($this->adminUser)
            ->test(UserManagement::class)
            ->assertViewHas('users', function ($users) {
                return $users->currentPage() === 1;
            })
            ->call('nextPage')
            ->assertViewHas('users', function ($users) {
                return $users->currentPage() === 2;
            })
            ->call('previousPage')
            ->assertViewHas('users', function ($users) {
                return $users->currentPage() === 1;
            });
    }

    /** @test */
    public function it_can_go_to_specific_page()
    {
        // Create many users
        User::factory()->count(25)->create();

        Livewire::actingAs($this->adminUser)
            ->test(UserManagement::class)
            ->call('gotoPage', 2)
            ->assertViewHas('users', function ($users) {
                return $users->currentPage() === 2;
            });
    }

    /** @test */
    public function it_shows_total_users_count()
    {
        User::factory()->count(3)->create();

        Livewire::actingAs($this->adminUser)
            ->test(UserManagement::class)
            ->assertSee('Total Pengguna:')
            ->assertSee('4'); // 1 admin + 3 created users
    }

    /** @test */
    public function unauthorized_user_cannot_access_component()
    {
        $regularUser = User::factory()->create();

        Livewire::actingAs($regularUser)
            ->test(UserManagement::class)
            ->assertStatus(403); // Should be forbidden if proper authorization is implemented
    }

    /** @test */
    public function it_handles_user_not_found_on_edit()
    {
        $nonExistentUserId = 999;

        Livewire::actingAs($this->adminUser)
            ->test(UserManagement::class)
            ->call('openEditModal', $nonExistentUserId)
            ->assertNotDispatched('openUserForm') // Should not dispatch if user not found
            ->assertSessionHas('error', 'Pengguna tidak ditemukan');
    }

    /** @test */
    public function it_handles_user_not_found_on_delete()
    {
        $nonExistentUserId = 999;

        Livewire::actingAs($this->adminUser)
            ->test(UserManagement::class)
            ->call('confirmDelete', $nonExistentUserId)
            ->assertSessionHas('error', 'Pengguna tidak ditemukan')
            ->assertSet('showDeleteModal', false);
    }
}
