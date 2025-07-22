<?php

namespace Tests\Feature\Auth;

use App\Models\Invite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        config()->set('app.registration', true);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_new_users_cannot_register_with_registration_disabled(): void
    {
        config()->set('app.registration', false);
        config()->set('app.invite.enabled', false);
        $this->get('/register')->assertRedirect(route('login', absolute: false));

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'username' => 'testuser',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_new_users_cannot_register_with_invalid_invite_code(): void
    {
        config()->set('app.registration', false);
        config()->set('app.invite.enabled', true);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => 'password',
            'password_confirmation' => 'password',
            'invite' => fake()->uuid(),
        ]);
        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_users_can_register_with_valid_invite_code(): void
    {
        config()->set('app.registration', false);
        config()->set('app.invite.enabled', true);

        $invite = Invite::factory()->create();
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => 'password',
            'password_confirmation' => 'password',
            'invite' => $invite->id,
        ]);
        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
