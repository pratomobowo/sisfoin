<?php

namespace Tests\Feature\Auth;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $entry = ActivityLog::query()->latest('id')->first();

        $this->assertNotNull($entry);
        $this->assertSame('auth', $entry->log_name);
        $this->assertSame('login', $entry->event);
        $this->assertSame('auth.session.login', $entry->action);
        $this->assertSame('auth', $entry->metadata['module'] ?? null);
        $this->assertSame('low', $entry->metadata['risk_level'] ?? null);
        $this->assertSame('success', $entry->metadata['result'] ?? null);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');

        $entry = ActivityLog::query()->latest('id')->first();

        $this->assertNotNull($entry);
        $this->assertSame('auth', $entry->log_name);
        $this->assertSame('logout', $entry->event);
        $this->assertSame('auth.session.logout', $entry->action);
    }
}
