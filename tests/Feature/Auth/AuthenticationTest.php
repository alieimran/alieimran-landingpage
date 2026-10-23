<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function enableTurnstile(): void
    {
        config(['services.turnstile' => [
            'enabled' => true,
            'site_key' => 'test-site-key',
            'secret_key' => 'test-secret',
            'hostname' => 'alieimran.com',
        ]]);
    }

    public function test_turnstile_widget_is_rendered_when_enabled(): void
    {
        $this->enableTurnstile();
        $this->get('/login')->assertOk()->assertSee('cf-turnstile')->assertDontSee('test-secret');
    }

    public function test_turnstile_blocks_missing_tokens_before_authentication(): void
    {
        $this->enableTurnstile();
        Http::fake();
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('cf-turnstile-response');
        $this->assertGuest();
        Http::assertNothingSent();
    }

    public function test_turnstile_requires_success_and_matching_hostname_and_action(): void
    {
        $this->enableTurnstile();
        foreach ([
            ['success' => false],
            ['success' => true, 'hostname' => 'attacker.example', 'action' => 'login'],
            ['success' => true, 'hostname' => 'alieimran.com', 'action' => 'other'],
        ] as $result) {
            Http::fake(['challenges.cloudflare.com/*' => Http::response($result)]);
            $user = User::factory()->create();
            $this->post('/login', ['email' => $user->email, 'password' => 'password', 'cf-turnstile-response' => 'token'])
                ->assertSessionHasErrors('cf-turnstile-response');
            $this->assertGuest();
        }
    }

    public function test_turnstile_allows_verified_logins(): void
    {
        $this->enableTurnstile();
        Http::fake(['challenges.cloudflare.com/*' => Http::response([
            'success' => true, 'hostname' => 'alieimran.com', 'action' => 'login',
        ])]);
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'password', 'cf-turnstile-response' => 'token'])
            ->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_turnstile_connection_failure_does_not_bypass_verification(): void
    {
        $this->enableTurnstile();
        Http::fake(['challenges.cloudflare.com/*' => Http::failedConnection()]);
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'password', 'cf-turnstile-response' => 'token'])
            ->assertSessionHasErrors('cf-turnstile-response');
        $this->assertGuest();
    }

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
    }
}
