<?php

namespace Tests\Feature\Controllers\Api;

use App\Jobs\SendApPostLikeActivity;
use App\Jobs\SendApPostUndoLikeActivity;
use App\Models\ActivityPubActor;
use App\Models\ActivityPubPost;
use App\Models\ActivityPubPostLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ActivityPubPostInteractionControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(): ActivityPubPost
    {
        $actor = ActivityPubActor::factory()->create();

        return ActivityPubPost::create([
            'activity_pub_actor_id' => $actor->id,
            'activity_id' => 'https://remote.example/activities/'.Str::uuid(),
            'url' => 'https://remote.example/posts/1',
            'content' => 'Hello world',
            'published_at' => now(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Authentifizierung
    // ──────────────────────────────────────────────────────────────────────────

    public function test_like_requires_authentication(): void
    {
        $post = $this->makePost();

        $this->postJson("/api/activitypub/posts/{$post->id}/likes")->assertStatus(401);
    }

    public function test_unlike_requires_authentication(): void
    {
        $post = $this->makePost();

        $this->deleteJson("/api/activitypub/posts/{$post->id}/likes")->assertStatus(401);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Unbekannter Post
    // ──────────────────────────────────────────────────────────────────────────

    public function test_like_returns_404_for_unknown_post(): void
    {
        Passport::actingAs(User::factory()->create());

        $this->postJson('/api/activitypub/posts/'.Str::uuid().'/likes')->assertStatus(404);
    }

    public function test_unlike_returns_404_for_unknown_post(): void
    {
        Passport::actingAs(User::factory()->create());

        $this->deleteJson('/api/activitypub/posts/'.Str::uuid().'/likes')->assertStatus(404);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/activitypub/posts/{postId}/likes
    // ──────────────────────────────────────────────────────────────────────────

    public function test_like_creates_like_and_dispatches_job(): void
    {
        Bus::fake();
        $user = User::factory()->create();
        Passport::actingAs($user);
        $post = $this->makePost();

        $response = $this->postJson("/api/activitypub/posts/{$post->id}/likes");

        $response->assertOk()->assertJson(['likedByUser' => true, 'likeCount' => 1]);
        $this->assertDatabaseHas('activity_pub_post_likes', [
            'user_id' => $user->id,
            'activity_pub_post_id' => $post->id,
        ]);
        Bus::assertDispatched(SendApPostLikeActivity::class);
    }

    public function test_like_is_idempotent_and_does_not_create_duplicate(): void
    {
        Bus::fake();
        Passport::actingAs(User::factory()->create());
        $post = $this->makePost();

        $this->postJson("/api/activitypub/posts/{$post->id}/likes")->assertOk();
        $response = $this->postJson("/api/activitypub/posts/{$post->id}/likes");

        $response->assertOk()->assertJson(['likedByUser' => true, 'likeCount' => 1]);
        $this->assertDatabaseCount('activity_pub_post_likes', 1);
        Bus::assertDispatchedTimes(SendApPostLikeActivity::class, 1);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DELETE /api/activitypub/posts/{postId}/likes
    // ──────────────────────────────────────────────────────────────────────────

    public function test_unlike_removes_like_and_dispatches_job(): void
    {
        Bus::fake();
        $user = User::factory()->create();
        Passport::actingAs($user);
        $post = $this->makePost();

        ActivityPubPostLike::create([
            'user_id' => $user->id,
            'activity_pub_post_id' => $post->id,
            'activity_id' => 'https://example.test/likes/1',
        ]);

        $response = $this->deleteJson("/api/activitypub/posts/{$post->id}/likes");

        $response->assertOk()->assertJson(['likedByUser' => false, 'likeCount' => 0]);
        $this->assertDatabaseMissing('activity_pub_post_likes', [
            'user_id' => $user->id,
            'activity_pub_post_id' => $post->id,
        ]);
        Bus::assertDispatched(SendApPostUndoLikeActivity::class);
    }

    public function test_unlike_when_not_liked_is_a_graceful_noop(): void
    {
        Bus::fake();
        Passport::actingAs(User::factory()->create());
        $post = $this->makePost();

        $response = $this->deleteJson("/api/activitypub/posts/{$post->id}/likes");

        $response->assertOk()->assertJson(['likedByUser' => false, 'likeCount' => 0]);
        Bus::assertNotDispatched(SendApPostUndoLikeActivity::class);
    }
}
