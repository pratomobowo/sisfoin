<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private $userService;
    private $testUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->userService = app(UserService::class);
        
        // Create test user with admin role
        $this->testUser = User::factory()->create();
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $this->testUser->assignRole($adminRole);
        
        // Set the authenticated user for testing
        $this->actingAs($this->testUser);
    }

    /** @test */
    public function it_can_get_users_with_pagination()
    {
        // Create additional users
        User::factory()->count(15)->create();

        $users = $this->userService->getUsers([], 10);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $users);
        $this->assertEquals(10, $users->perPage());
        $this->assertTrue($users->total() >= 15); // At least 15 users (1 from setUp + 15 created)
    }

    /** @test */
    public function it_can_filter_users_by_search()
    {
        $searchUser = User::factory()->create(['name' => 'John Doe Search Test']);
        User::factory()->create(['name' => 'Jane Smith']);

        $users = $this->userService->getUsers(['search' => 'John Doe'], 10);

        $this->assertTrue($users->contains('name', 'John Doe Search Test'));
        $this->assertFalse($users->contains('name', 'Jane Smith'));
    }

    /** @test */
    public function it_can_filter_users_by_role()
    {
        $adminRole = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        $userRole = Role::create(['name' => 'user', 'guard_name' => 'web']);

        $adminUser = User::factory()->create();
        $regularUser = User::factory()->create();

        $adminUser->assignRole($adminRole);
        $regularUser->assignRole($userRole);

        $users = $this->userService->getUsers(['role' => $adminRole->id], 10);

        $this->assertTrue($users->contains('id', $adminUser->id));
        $this->assertFalse($users->contains('id', $regularUser->id));
    }

    /** @test */
    public function it_can_get_user_by_id()
    {
        $user = User::factory()->create();

        $foundUser = $this->userService->getUserById($user->id);

        $this->assertEquals($user->id, $foundUser->id);
        $this->assertEquals($user->email, $foundUser->email);
    }

    /** @test */
    public function it_can_create_new_user()
    {
        $role = Role::create(['name' => 'staff', 'guard_name' => 'web']);

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$role->id],
        ];

        $user = $this->userService->createUser($userData);

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertTrue($user->hasRole('staff'));
    }

    /** @test */
    public function it_can_update_existing_user()
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'roles' => [$role->id],
        ];

        $updatedUser = $this->userService->updateUser($user->id, $updateData);

        $this->assertEquals('Updated Name', $updatedUser->name);
        $this->assertEquals('updated@example.com', $updatedUser->email);
        $this->assertTrue($updatedUser->hasRole('editor'));
    }

    /** @test */
    public function it_can_delete_user()
    {
        $user = User::factory()->create();

        $result = $this->userService->deleteUser($user->id);

        $this->assertTrue($result);
        $this->assertNull(User::find($user->id));
    }

    /** @test */
    public function it_cannot_delete_own_account()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot delete own account');

        $this->userService->deleteUser($this->testUser->id);
    }

    /** @test */
    public function it_can_get_all_roles()
    {
        Role::create(['name' => 'test-role-1', 'guard_name' => 'web']);
        Role::create(['name' => 'test-role-2', 'guard_name' => 'web']);
        Role::create(['name' => 'test-role-3', 'guard_name' => 'web']);

        $roles = $this->userService->getAllRoles();

        $this->assertGreaterThan(0, $roles->count());
    }

    /** @test */
    public function it_can_assign_roles_to_user()
    {
        $user = User::factory()->create();
        $role1 = Role::create(['name' => 'role1', 'guard_name' => 'web']);
        $role2 = Role::create(['name' => 'role2', 'guard_name' => 'web']);

        $updatedUser = $this->userService->assignRoles($user->id, [$role1->id, $role2->id]);

        $this->assertTrue($updatedUser->hasRole('role1'));
        $this->assertTrue($updatedUser->hasRole('role2'));
    }

    /** @test */
    public function it_can_enable_fingerprint_access()
    {
        $user = User::factory()->create();

        $updatedUser = $this->userService->enableFingerprintAccess($user->id, '123456');

        $this->assertTrue($updatedUser->fingerprint_enabled);
        $this->assertEquals('123456', $updatedUser->fingerprint_pin);
    }

    /** @test */
    public function it_can_disable_fingerprint_access()
    {
        $user = User::factory()->create([
            'fingerprint_enabled' => true,
            'fingerprint_pin' => '123456'
        ]);

        $updatedUser = $this->userService->disableFingerprintAccess($user->id);

        $this->assertFalse($updatedUser->fingerprint_enabled);
    }

    /** @test */
    public function it_can_set_fingerprint_pin()
    {
        $user = User::factory()->create();

        $updatedUser = $this->userService->setFingerprintPin($user->id, '654321');

        $this->assertEquals('654321', $updatedUser->fingerprint_pin);
    }

    /** @test */
    public function it_can_get_users_with_fingerprint_enabled()
    {
        // Create users with fingerprint enabled
        User::factory()->count(3)->create([
            'fingerprint_enabled' => true,
            'fingerprint_pin' => '123456'
        ]);

        // Create users without fingerprint
        User::factory()->count(2)->create([
            'fingerprint_enabled' => false
        ]);

        $users = $this->userService->getUsersWithFingerprintEnabled();

        $this->assertEquals(3, $users->count());
        $users->each(function ($user) {
            $this->assertTrue($user->fingerprint_enabled);
            $this->assertNotNull($user->fingerprint_pin);
        });
    }

    /** @test */
    public function it_can_find_user_by_fingerprint_pin()
    {
        $user = User::factory()->create([
            'fingerprint_enabled' => true,
            'fingerprint_pin' => '999999'
        ]);

        $foundUser = $this->userService->findUserByFingerprintPin('999999');

        $this->assertEquals($user->id, $foundUser->id);
    }

    /** @test */
    public function it_can_get_user_statistics()
    {
        User::factory()->count(5)->create(['email_verified_at' => now()]);
        User::factory()->count(3)->create(['email_verified_at' => null]);

        $stats = $this->userService->getUserStatistics();

        $this->assertArrayHasKey('total_users', $stats);
        $this->assertArrayHasKey('active_users', $stats);
        $this->assertArrayHasKey('users_with_fingerprint', $stats);
        $this->assertEquals(9, $stats['total_users']); // 1 from setUp + 5 + 3
        $this->assertEquals(6, $stats['active_users']); // 1 from setUp + 5
    }

    /** @test */
    public function it_can_search_users()
    {
        $user1 = User::factory()->create(['name' => 'Alice Johnson']);
        $user2 = User::factory()->create(['name' => 'Bob Smith']);
        $user3 = User::factory()->create(['email' => 'alice.test@example.com']);

        $results = $this->userService->searchUsers('Alice');

        $this->assertTrue($results->contains('id', $user1->id));
        $this->assertTrue($results->contains('id', $user3->id));
        $this->assertFalse($results->contains('id', $user2->id));
    }

    /** @test */
    public function it_can_bulk_delete_users()
    {
        $users = User::factory()->count(3)->create();
        $userIds = $users->pluck('id')->toArray();

        $results = $this->userService->bulkDeleteUsers($userIds);

        $this->assertCount(3, $results['success']);
        $this->assertCount(0, $results['failed']);
        
        // Verify users are deleted
        foreach ($userIds as $userId) {
            $this->assertNull(User::find($userId));
        }
    }

    /** @test */
    public function it_can_bulk_assign_roles()
    {
        $users = User::factory()->count(3)->create();
        $role = Role::create(['name' => 'bulk-role', 'guard_name' => 'web']);
        
        $userIds = $users->pluck('id')->toArray();
        $results = $this->userService->bulkAssignRoles($userIds, [$role->id]);

        $this->assertCount(3, $results['success']);
        $this->assertCount(0, $results['failed']);
        
        // Verify roles are assigned
        foreach ($users as $user) {
            $this->assertTrue($user->fresh()->hasRole('bulk-role'));
        }
    }
}
