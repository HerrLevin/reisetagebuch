<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\Invite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class InviteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.invite.enabled' => true]);
        config(['app.invite.whitelist' => []]);
    }

    public function test_index_returns_user_invites(): void
    {
        $user = User::factory()->create();
        Invite::factory()->count(3)->create(['user_id' => $user->id]);
        Invite::factory()->count(2)->create();

        Passport::actingAs($user);
        $response = $this->getJson(route('api.invites.index'));

        $response->assertOk();
        $response->assertJsonCount(3);
    }

    public function test_store_creates_invite(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($user);
        $response = $this->postJson(route('api.invites.store'));

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseCount('invites', 1);
        $this->assertDatabaseHas('invites', ['user_id' => $user->id]);
    }

    public function test_store_creates_invite_with_expiration(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($user);
        $response = $this->postJson(route('api.invites.store'), [
            'expires_at' => '2025-12-31',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseCount('invites', 1);
    }

    public function test_destroy_deletes_invite(): void
    {
        $user = User::factory()->create();
        $invite = Invite::factory()->create(['user_id' => $user->id]);

        Passport::actingAs($user);
        $response = $this->deleteJson(route('api.invites.destroy', $invite->id));

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('invites', ['id' => $invite->id]);
    }

    public function test_destroy_cannot_delete_other_users_invite(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $invite = Invite::factory()->create(['user_id' => $otherUser->id]);

        Passport::actingAs($user);
        $response = $this->deleteJson(route('api.invites.destroy', $invite->id));

        $response->assertForbidden();
        $this->assertDatabaseHas('invites', ['id' => $invite->id]);
    }

    public function test_unauthenticated_user_cannot_access_invites(): void
    {
        $response = $this->getJson(route('api.invites.index'));

        $response->assertUnauthorized();
    }

    public function test_store_forbidden_when_invite_disabled(): void
    {
        config(['app.invite.enabled' => false]);

        $user = User::factory()->create();

        Passport::actingAs($user);
        $response = $this->postJson(route('api.invites.store'));

        $response->assertForbidden();
    }

    public function test_store_forbidden_when_user_not_in_whitelist(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        config(['app.invite.whitelist' => [$otherUser->id]]);

        Passport::actingAs($user);
        $response = $this->postJson(route('api.invites.store'));

        $response->assertForbidden();
    }
}
