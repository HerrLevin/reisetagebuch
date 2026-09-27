<?php

namespace Tests\Repository;

use App\Models\ActivityPubActor;
use App\Models\ActivityPubPost;
use App\Models\ActivityPubRemoteFollow;
use App\Models\User;
use App\Repositories\ActivityPubPostRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ActivityPubPostRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private function follow(User $user, ActivityPubActor $actor): void
    {
        ActivityPubRemoteFollow::create([
            'local_user_id' => $user->id,
            'remote_actor_id' => $actor->actor_uri,
            'remote_actor_inbox_url' => $actor->inbox_url,
            'follow_activity_id' => 'https://remote.example/activities/'.Str::uuid(),
            'state' => 'accepted',
        ]);
    }

    private function makePost(ActivityPubActor $actor, array $overrides = []): ActivityPubPost
    {
        return ActivityPubPost::create(array_merge([
            'activity_pub_actor_id' => $actor->id,
            'activity_id' => 'https://remote.example/notes/'.Str::uuid(),
            'content' => 'Hello world',
            'published_at' => Carbon::now()->subMinute(),
        ], $overrides));
    }

    public function test_top_level_post_from_followed_actor_is_shown(): void
    {
        $user = User::factory()->create();
        $actor = ActivityPubActor::factory()->create();
        $this->follow($user, $actor);

        $post = $this->makePost($actor);

        $repo = new ActivityPubPostRepository;
        $result = $repo->getForFollowedActors($user->id, Carbon::now(), 25);

        $this->assertCount(1, $result);
        $this->assertSame($post->id, $result->first()->id);
    }

    public function test_reply_mentioning_only_unfollowed_actor_is_hidden(): void
    {
        $user = User::factory()->create();
        $followedActor = ActivityPubActor::factory()->create();
        $unfollowedActor = ActivityPubActor::factory()->create();
        $this->follow($user, $followedActor);

        $this->makePost($followedActor, [
            'in_reply_to' => 'https://remote.example/notes/original',
            'mentions' => [$unfollowedActor->actor_uri],
        ]);

        $repo = new ActivityPubPostRepository;
        $result = $repo->getForFollowedActors($user->id, Carbon::now(), 25);

        $this->assertCount(0, $result);
    }

    public function test_reply_mentioning_also_followed_actor_is_shown(): void
    {
        $user = User::factory()->create();
        $followedActor = ActivityPubActor::factory()->create();
        $alsoFollowedActor = ActivityPubActor::factory()->create();
        $this->follow($user, $followedActor);
        $this->follow($user, $alsoFollowedActor);

        $reply = $this->makePost($followedActor, [
            'in_reply_to' => 'https://remote.example/notes/original',
            'mentions' => [$alsoFollowedActor->actor_uri],
        ]);

        $repo = new ActivityPubPostRepository;
        $result = $repo->getForFollowedActors($user->id, Carbon::now(), 25);

        $this->assertCount(1, $result);
        $this->assertSame($reply->id, $result->first()->id);
    }

    public function test_self_reply_thread_is_shown_even_without_mentions(): void
    {
        $user = User::factory()->create();
        $actor = ActivityPubActor::factory()->create();
        $this->follow($user, $actor);

        $original = $this->makePost($actor, [
            'published_at' => Carbon::now()->subMinutes(5),
        ]);
        $reply = $this->makePost($actor, [
            'in_reply_to' => $original->activity_id,
            'mentions' => [],
            'published_at' => Carbon::now()->subMinute(),
        ]);

        $repo = new ActivityPubPostRepository;
        $result = $repo->getForFollowedActors($user->id, Carbon::now(), 25);

        $ids = $result->pluck('id')->all();
        $this->assertContains($original->id, $ids);
        $this->assertContains($reply->id, $ids);
    }

    public function test_reply_mentioning_also_followed_and_unfollowed_actor_is_shown(): void
    {
        $user = User::factory()->create();
        $followedActor = ActivityPubActor::factory()->create();
        $alsoFollowedActor = ActivityPubActor::factory()->create();
        $unfollowedActor = ActivityPubActor::factory()->create();
        $this->follow($user, $followedActor);
        $this->follow($user, $alsoFollowedActor);

        $reply = $this->makePost($followedActor, [
            'in_reply_to' => 'https://remote.example/notes/original',
            'mentions' => [$alsoFollowedActor->actor_uri, $unfollowedActor->actor_uri],
        ]);

        $repo = new ActivityPubPostRepository;
        $result = $repo->getForFollowedActors($user->id, Carbon::now(), 25);

        $this->assertCount(1, $result);
        $this->assertSame($reply->id, $result->first()->id);
    }
}
