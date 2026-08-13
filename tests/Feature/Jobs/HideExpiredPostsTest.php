<?php

namespace Tests\Feature\Jobs;

use App\Enums\Visibility;
use App\Hydrators\PostHydrator;
use App\Jobs\ActivityPub\PushDeleteToMastodon;
use App\Jobs\HideExpiredPostJob;
use App\Jobs\HideExpiredPosts;
use App\Jobs\TraewellingEditPostJob;
use App\Models\Post;
use App\Models\TransportPost;
use App\Models\TransportTrip;
use App\Models\User;
use App\Repositories\PostRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use ReflectionProperty;
use Tests\TestCase;

class HideExpiredPostsTest extends TestCase
{
    use RefreshDatabase;

    private function jobProperty(object $job, string $name): mixed
    {
        return new ReflectionProperty($job, $name)->getValue($job);
    }

    private function userWithHideAfter(?float $hidePostsAfter): User
    {
        // UserFactory::afterCreating() already attaches a default UserSettings row,
        // so update it in place instead of creating a second (orphaning) one.
        $user = User::factory()->create();
        $user->settings->update([
            'hide_posts_after' => $hidePostsAfter === null ? null : (string) $hidePostsAfter,
        ]);

        return $user;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PostRepository::getPostsToAutoHide
    // ──────────────────────────────────────────────────────────────────────────

    public function test_finds_expired_public_post(): void
    {
        $user = $this->userWithHideAfter(1);
        $post = Post::factory()->for($user)->create([
            'visibility' => Visibility::PUBLIC,
            'created_at' => now()->subDays(2),
        ]);

        $result = app(PostRepository::class)->getPostsToAutoHide();

        $this->assertTrue($result->pluck('id')->contains($post->id));
    }

    public function test_ignores_post_not_yet_expired(): void
    {
        $user = $this->userWithHideAfter(7);
        Post::factory()->for($user)->create([
            'visibility' => Visibility::PUBLIC,
            'created_at' => now()->subDays(1),
        ]);

        $result = app(PostRepository::class)->getPostsToAutoHide();

        $this->assertCount(0, $result);
    }

    public function test_ignores_already_private_post(): void
    {
        $user = $this->userWithHideAfter(1);
        Post::factory()->for($user)->create([
            'visibility' => Visibility::PRIVATE,
            'created_at' => now()->subDays(2),
        ]);

        $result = app(PostRepository::class)->getPostsToAutoHide();

        $this->assertCount(0, $result);
    }

    public function test_ignores_user_without_hide_posts_after_setting(): void
    {
        $user = $this->userWithHideAfter(null);
        Post::factory()->for($user)->create([
            'visibility' => Visibility::PUBLIC,
            'created_at' => now()->subDays(30),
        ]);

        $result = app(PostRepository::class)->getPostsToAutoHide();

        $this->assertCount(0, $result);
    }

    public function test_ignores_user_without_settings_at_all(): void
    {
        $user = User::factory()->create();
        $user->settings()->delete();
        Post::factory()->for($user)->create([
            'visibility' => Visibility::PUBLIC,
            'created_at' => now()->subDays(30),
        ]);

        $result = app(PostRepository::class)->getPostsToAutoHide();

        $this->assertCount(0, $result);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HideExpiredPosts (sweep job)
    // ──────────────────────────────────────────────────────────────────────────

    public function test_sweep_dispatches_hide_job_for_each_expired_post(): void
    {
        $user = $this->userWithHideAfter(0.25);
        $post = Post::factory()->for($user)->create([
            'visibility' => Visibility::PUBLIC,
            'created_at' => now()->subDay(),
        ]);

        Bus::fake();

        (new HideExpiredPosts)->handle(app(PostRepository::class));

        Bus::assertDispatched(HideExpiredPostJob::class, function (HideExpiredPostJob $job) use ($post) {
            return $this->jobProperty($job, 'postId') === $post->id;
        });
    }

    public function test_sweep_does_not_dispatch_for_unaffected_posts(): void
    {
        $user = $this->userWithHideAfter(null);
        Post::factory()->for($user)->create([
            'visibility' => Visibility::PUBLIC,
            'created_at' => now()->subDays(30),
        ]);

        Bus::fake();

        (new HideExpiredPosts)->handle(app(PostRepository::class));

        Bus::assertNotDispatched(HideExpiredPostJob::class);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HideExpiredPostJob
    // ──────────────────────────────────────────────────────────────────────────

    public function test_hide_job_sets_post_to_private(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create(['visibility' => Visibility::PUBLIC]);

        Bus::fake();

        (new HideExpiredPostJob($post->id))->handle(app(PostRepository::class), app(PostHydrator::class));

        $this->assertSame(Visibility::PRIVATE, $post->fresh()->visibility);
    }

    public function test_hide_job_dispatches_mastodon_delete(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create(['visibility' => Visibility::PUBLIC]);

        Bus::fake();

        (new HideExpiredPostJob($post->id))->handle(app(PostRepository::class), app(PostHydrator::class));

        Bus::assertDispatched(PushDeleteToMastodon::class, function (PushDeleteToMastodon $job) use ($post, $user) {
            return $this->jobProperty($job, 'postId') === $post->id
                && $this->jobProperty($job, 'userId') === $user->id
                && $this->jobProperty($job, 'username') === $user->username;
        });
    }

    public function test_hide_job_dispatches_traewelling_edit_for_transport_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create(['visibility' => Visibility::PUBLIC]);
        $trip = TransportTrip::factory()->create();
        TransportPost::factory()->create([
            'post_id' => $post->id,
            'transport_trip_id' => $trip->id,
        ]);

        Bus::fake();

        (new HideExpiredPostJob($post->id))->handle(app(PostRepository::class), app(PostHydrator::class));

        Bus::assertDispatched(TraewellingEditPostJob::class, function (TraewellingEditPostJob $job) use ($post) {
            return $this->jobProperty($job, 'postId') === $post->id;
        });
    }

    public function test_hide_job_does_not_dispatch_traewelling_edit_for_non_transport_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create(['visibility' => Visibility::PUBLIC]);

        Bus::fake();

        (new HideExpiredPostJob($post->id))->handle(app(PostRepository::class), app(PostHydrator::class));

        Bus::assertNotDispatched(TraewellingEditPostJob::class);
    }

    public function test_hide_job_does_nothing_for_missing_post(): void
    {
        Bus::fake();

        (new HideExpiredPostJob('00000000-0000-7000-8000-000000000000'))
            ->handle(app(PostRepository::class), app(PostHydrator::class));

        Bus::assertNotDispatched(PushDeleteToMastodon::class);
        Bus::assertNotDispatched(TraewellingEditPostJob::class);
    }

    public function test_hide_job_does_nothing_for_already_private_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create(['visibility' => Visibility::PRIVATE]);

        Bus::fake();

        (new HideExpiredPostJob($post->id))->handle(app(PostRepository::class), app(PostHydrator::class));

        Bus::assertNotDispatched(PushDeleteToMastodon::class);
    }
}
