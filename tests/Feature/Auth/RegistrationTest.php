<?php

namespace Tests\Feature\Auth;

use App\Models\Invite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private array $payload = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'username' => 'testuser',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_registration_form_is_rendered(): void
    {
        config()->set('app.registration', true);

        $this->get('/register')->assertOk()->assertSee('name="username"', false);
    }

    public function test_new_users_can_register(): void
    {
        config()->set('app.registration', true);

        $this->post('/register', $this->payload)->assertRedirect('/home');

        $user = User::where('username', 'testuser')->firstOrFail();
        $this->assertAuthenticatedAs($user, 'web');
        $this->assertNotNull($user->profile);
    }

    public function test_new_users_cannot_register_with_registration_disabled(): void
    {
        config()->set('app.registration', false);
        config()->set('app.invite.enabled', false);

        $this->get('/register')->assertForbidden();
        $this->post('/register', $this->payload)->assertForbidden();
    }

    public function test_new_users_cannot_register_with_invalid_invite_code(): void
    {
        config()->set('app.registration', false);
        config()->set('app.invite.enabled', true);

        $this->post('/register', [...$this->payload, 'invite' => fake()->uuid()])
            ->assertSessionHasErrors('invite');
        $this->assertGuest('web');
    }

    public function test_users_can_register_with_valid_invite_code(): void
    {
        config()->set('app.registration', false);
        config()->set('app.invite.enabled', true);

        $invite = Invite::factory()->create();

        $this->post('/register', [...$this->payload, 'invite' => $invite->id])
            ->assertRedirect('/home');

        $this->assertNotNull($invite->fresh()->used_at);
    }
}
