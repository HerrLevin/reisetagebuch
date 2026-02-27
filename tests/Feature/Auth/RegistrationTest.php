<?php

namespace Tests\Feature\Auth;

use App\Models\Invite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_can_register(): void
    {
        config()->set('app.registration', true);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_new_users_cannot_register_with_registration_disabled(): void
    {
        config()->set('app.registration', false);
        config()->set('app.invite.enabled', false);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertForbidden();
    }

    public function test_new_users_cannot_register_with_invalid_invite_code(): void
    {
        config()->set('app.registration', false);
        config()->set('app.invite.enabled', true);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => 'password',
            'password_confirmation' => 'password',
            'invite' => fake()->uuid(),
        ]);

        $response->assertUnprocessable();
    }

    public function test_users_can_register_with_valid_invite_code(): void
    {
        config()->set('app.registration', false);
        config()->set('app.invite.enabled', true);

        $invite = Invite::factory()->create();
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => 'password',
            'password_confirmation' => 'password',
            'invite' => $invite->id,
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure(['token', 'user']);
    }
}
