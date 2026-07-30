<?php

namespace Tests\Repository;

use App\Models\ActivityPubFollower;
use App\Models\User;
use App\Repositories\ActivityPubFollowerRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityPubFollowerRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_for_user_returns_followers_ordered_by_created_at_desc(): void
    {
        $user = User::factory()->create();

        $older = ActivityPubFollower::factory()->create([
            'followed_user_id' => $user->id,
            'created_at' => now()->subDay(),
        ]);
        $newer = ActivityPubFollower::factory()->create([
            'followed_user_id' => $user->id,
            'created_at' => now(),
        ]);

        $result = new ActivityPubFollowerRepository()->listForUser($user->id);

        $this->assertSame([$newer->id, $older->id], $result->pluck('id')->all());
    }

    public function test_list_for_user_returns_empty_collection_for_user_with_no_followers(): void
    {
        $user = User::factory()->create();

        $result = new ActivityPubFollowerRepository()->listForUser($user->id);

        $this->assertTrue($result->isEmpty());
    }

    public function test_list_for_user_does_not_return_other_users_followers(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        ActivityPubFollower::factory()->create(['followed_user_id' => $otherUser->id]);

        $result = new ActivityPubFollowerRepository()->listForUser($user->id);

        $this->assertTrue($result->isEmpty());
    }
}
