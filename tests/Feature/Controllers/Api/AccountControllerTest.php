<?php

namespace Feature\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($user);

        $response = $this
            ->patchJson('/api/account', [
                'name' => 'Test User',
                'username' => 'testuser',
                'email' => 'test@example.com',
            ]);

        $response->assertNoContent();

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($user);
        $response = $this
            ->patchJson('/api/account', [
                'name' => 'Test User',
                'username' => 'testuser',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this
            ->deleteJson('/api/account', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors();

        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this
            ->deleteJson('/api/account', [
                'password' => 'wrong-password',
            ]);

        $response->assertStatus(422);
        $this->assertContains('The password is incorrect.', $response->json());

        $this->assertNotNull($user->fresh());
    }
}
