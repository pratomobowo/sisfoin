<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAuthorizationHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_role_middleware_authorizes_assigned_role_without_switching_active_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole(['staff', 'admin-sdm']);

        $this->actingAs($user);
        setActiveRole('staff');

        $this->get(route('sdm.dashboard'))->assertOk();

        $this->assertSame('staff', getActiveRole());
    }

    public function test_privileged_user_cannot_self_delete_profile(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('secret-password'),
        ]);
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'secret-password'])
            ->assertSessionHasErrors('password', null, 'userDeletion');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
