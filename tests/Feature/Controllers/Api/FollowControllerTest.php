<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\Follow;
use App\Models\User;
use App\Models\UserSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class FollowControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_followers_is_public_for_default_account(): void
    {
        $user = User::factory()->create();
        $follower = User::factory()->create();
        Follow::create(['origin_user_id' => $follower->id, 'target_user_id' => $user->id]);

        $response = $this->getJson("/api/users/{$user->id}/followers");

        $response->assertOk()->assertJsonCount(1);
        $this->assertSame($follower->id, $response->json('0.id'));
    }

    public function test_get_followings_is_public_for_default_account(): void
    {
        $user = User::factory()->create();
        $followed = User::factory()->create();
        Follow::create(['origin_user_id' => $user->id, 'target_user_id' => $followed->id]);

        $response = $this->getJson("/api/users/{$user->id}/followings");

        $response->assertOk()->assertJsonCount(1);
        $this->assertSame($followed->id, $response->json('0.id'));
    }

    public function test_get_followers_returns_empty_array_for_user_with_no_followers(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}/followers");

        $response->assertOk()->assertExactJson([]);
    }

    public function test_get_followers_for_private_account_requires_ownership_or_follow(): void
    {
        $user = User::factory()->create();
        UserSettings::where('user_id', $user->id)->update(['requires_follow_request' => true]);

        $stranger = User::factory()->create();
        $follower = User::factory()->create();
        Follow::create(['origin_user_id' => $follower->id, 'target_user_id' => $user->id]);

        $this->getJson("/api/users/{$user->id}/followers")->assertForbidden();

        Passport::actingAs($stranger, ['*']);
        $this->getJson("/api/users/{$user->id}/followers")->assertForbidden();

        Passport::actingAs($follower, ['*']);
        $this->getJson("/api/users/{$user->id}/followers")->assertOk();

        Passport::actingAs($user, ['*']);
        $this->getJson("/api/users/{$user->id}/followers")->assertOk();
    }

    public function test_get_followings_for_private_account_requires_ownership_or_follow(): void
    {
        $user = User::factory()->create();
        UserSettings::where('user_id', $user->id)->update(['requires_follow_request' => true]);

        $stranger = User::factory()->create();
        $follower = User::factory()->create();
        Follow::create(['origin_user_id' => $follower->id, 'target_user_id' => $user->id]);

        $this->getJson("/api/users/{$user->id}/followings")->assertForbidden();

        Passport::actingAs($stranger, ['*']);
        $this->getJson("/api/users/{$user->id}/followings")->assertForbidden();

        Passport::actingAs($follower, ['*']);
        $this->getJson("/api/users/{$user->id}/followings")->assertOk();

        Passport::actingAs($user, ['*']);
        $this->getJson("/api/users/{$user->id}/followings")->assertOk();
    }
}
