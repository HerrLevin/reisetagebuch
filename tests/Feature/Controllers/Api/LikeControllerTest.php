<?php

namespace Tests\Feature\Controllers\Api;

use App\Enums\Visibility;
use App\Models\ActivityPubActor;
use App\Models\ActivityPubLike;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class LikeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_includes_local_and_remote_likes(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $author->id]);

        $localLiker = User::factory()->create();
        Like::create(['user_id' => $localLiker->id, 'post_id' => $post->id]);

        $remoteActor = ActivityPubActor::factory()->create([
            'actor_uri' => 'https://remote.example/users/bob',
            'preferred_username' => 'bob',
            'display_name' => 'Bob Remote',
        ]);
        ActivityPubLike::create([
            'actor_id' => $remoteActor->actor_uri,
            'post_id' => $post->id,
            'activity_id' => 'https://remote.example/activities/like-1',
        ]);

        Passport::actingAs($author);
        $response = $this->getJson(route('posts.likes', ['post' => $post->id]));

        $response->assertOk();
        $response->assertJsonCount(2);
        $response->assertJsonFragment(['username' => $localLiker->username]);
        $response->assertJsonFragment(['name' => 'Bob Remote']);
    }

    public function test_index_resolves_remote_liker_without_known_actor(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $author->id]);

        ActivityPubLike::create([
            'actor_id' => 'https://remote.example/users/unknown',
            'post_id' => $post->id,
            'activity_id' => 'https://remote.example/activities/like-2',
        ]);

        Passport::actingAs($author);
        $response = $this->getJson(route('posts.likes', ['post' => $post->id]));

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['profileUrl' => 'https://remote.example/users/unknown']);
    }

    public function test_post_likes_count_includes_remote_likes(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $author->id]);

        $localLiker = User::factory()->create();
        Like::create(['user_id' => $localLiker->id, 'post_id' => $post->id]);

        ActivityPubLike::create([
            'actor_id' => 'https://remote.example/users/bob',
            'post_id' => $post->id,
            'activity_id' => 'https://remote.example/activities/like-1',
        ]);

        Passport::actingAs($author);
        $response = $this->getJson(route('api.posts.show', ['post' => $post->id]));

        $response->assertOk();
        $response->assertJsonFragment(['likesCount' => 2]);
    }

    public function test_authenticated_user_can_like_a_post_with_only_authenticated_visibility(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $author->id, 'visibility' => Visibility::ONLY_AUTHENTICATED]);

        $liker = User::factory()->create();
        Passport::actingAs($liker);

        $response = $this->postJson(route('posts.like', ['postId' => $post->id]));

        $response->assertOk();
        $this->assertDatabaseHas('likes', ['user_id' => $liker->id, 'post_id' => $post->id]);
    }

    public function test_authenticated_user_can_unlike_a_post_with_only_authenticated_visibility(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $author->id, 'visibility' => Visibility::ONLY_AUTHENTICATED]);

        $liker = User::factory()->create();
        Like::create(['user_id' => $liker->id, 'post_id' => $post->id]);

        Passport::actingAs($liker);
        $response = $this->deleteJson(route('posts.unlike', ['postId' => $post->id]));

        $response->assertOk();
        $this->assertDatabaseMissing('likes', ['user_id' => $liker->id, 'post_id' => $post->id]);
    }

    public function test_authenticated_user_can_list_likes_on_a_post_with_only_authenticated_visibility(): void
    {
        $author = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $author->id, 'visibility' => Visibility::ONLY_AUTHENTICATED]);

        $liker = User::factory()->create();
        Like::create(['user_id' => $liker->id, 'post_id' => $post->id]);

        $viewer = User::factory()->create();
        Passport::actingAs($viewer);
        $response = $this->getJson(route('posts.likes', ['post' => $post->id]));

        $response->assertOk();
        $response->assertJsonCount(1);
    }
}
