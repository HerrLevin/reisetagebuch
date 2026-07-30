<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_followers_is_public_and_returns_users(): void
    {
        $user = User::factory()->create();
        $follower = User::factory()->create();
        Follow::create(['origin_user_id' => $follower->id, 'target_user_id' => $user->id]);

        $response = $this->getJson("/api/users/{$user->id}/followers");

        $response->assertOk()->assertJsonCount(1);
        $this->assertSame($follower->id, $response->json('0.id'));
    }

    public function test_get_followings_is_public_and_returns_users(): void
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
}
