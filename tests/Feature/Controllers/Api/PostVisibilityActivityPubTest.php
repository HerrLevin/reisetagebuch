<?php

namespace Tests\Feature\Controllers\Api;

use App\Enums\Visibility;
use App\Jobs\ActivityPub\PushDeleteToMastodon;
use App\Jobs\ActivityPub\PushPostToMastodon;
use App\Jobs\ActivityPub\PushUpdateToMastodon;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Laravel\Passport\Passport;
use PHPUnit\Framework\Attributes\TestWith;
use ReflectionProperty;
use Tests\TestCase;

class PostVisibilityActivityPubTest extends TestCase
{
    use RefreshDatabase;

    private function jobProperty(object $job, string $name): mixed
    {
        return new ReflectionProperty($job, $name)->getValue($job);
    }

    #[TestWith([Visibility::PUBLIC, Visibility::PRIVATE])]
    #[TestWith([Visibility::PUBLIC, Visibility::ONLY_AUTHENTICATED])]
    #[TestWith([Visibility::UNLISTED, Visibility::PRIVATE])]
    #[TestWith([Visibility::UNLISTED, Visibility::ONLY_AUTHENTICATED])]
    public function test_making_a_public_post_private_sends_a_delete_activity(Visibility $startVisibility, Visibility $endVisibility): void
    {
        Bus::fake();

        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => $startVisibility,
        ]);

        Passport::actingAs($user);
        $response = $this->patchJson(route('posts.update', ['postId' => $post->id]), [
            'visibility' => $endVisibility->value,
            'id' => $post->id,
        ]);

        $response->assertOk();

        Bus::assertDispatched(PushDeleteToMastodon::class, function (PushDeleteToMastodon $job) use ($post) {
            return $this->jobProperty($job, 'postId') === $post->id;
        });
        Bus::assertNotDispatched(PushUpdateToMastodon::class);
        Bus::assertNotDispatched(PushPostToMastodon::class);
    }

    #[TestWith([Visibility::PRIVATE, Visibility::PUBLIC])]
    #[TestWith([Visibility::PRIVATE, Visibility::UNLISTED])]
    #[TestWith([Visibility::ONLY_AUTHENTICATED, Visibility::PUBLIC])]
    #[TestWith([Visibility::ONLY_AUTHENTICATED, Visibility::UNLISTED])]
    public function test_making_a_private_post_public_sends_a_create_activity(Visibility $startVisibility, Visibility $endVisibility): void
    {
        Bus::fake();

        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => $startVisibility,
        ]);

        Passport::actingAs($user);
        $response = $this->patchJson(route('posts.update', ['postId' => $post->id]), [
            'visibility' => $endVisibility->value,
            'id' => $post->id,
        ]);

        $response->assertOk();

        Bus::assertDispatched(PushPostToMastodon::class, function (PushPostToMastodon $job) use ($post) {
            return $this->jobProperty($job, 'postId') === $post->id;
        });
        Bus::assertNotDispatched(PushUpdateToMastodon::class);
        Bus::assertNotDispatched(PushDeleteToMastodon::class);
    }

    #[TestWith([Visibility::PUBLIC])]
    #[TestWith([Visibility::UNLISTED])]
    #[TestWith([Visibility::ONLY_AUTHENTICATED])]
    public function test_editing_a_public_post_without_changing_visibility_sends_an_update_activity(Visibility $visibility): void
    {
        Bus::fake();

        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => $visibility,
            'body' => 'old body',
        ]);

        Passport::actingAs($user);
        $response = $this->patchJson(route('posts.update', ['postId' => $post->id]), [
            'id' => $post->id,
            'visibility' => $visibility->value,
            'body' => 'new body',
        ]);

        $response->assertOk();

        Bus::assertDispatched(PushUpdateToMastodon::class, function (PushUpdateToMastodon $job) use ($post) {
            return $this->jobProperty($job, 'postId') === $post->id;
        });
        Bus::assertNotDispatched(PushPostToMastodon::class);
        Bus::assertNotDispatched(PushDeleteToMastodon::class);
    }

    #[TestWith([Visibility::PRIVATE])]
    #[TestWith([Visibility::UNLISTED])]
    #[TestWith([Visibility::ONLY_AUTHENTICATED])]
    public function test_editing_a_private_post_without_changing_visibility_does_not_federate(Visibility $visibility): void
    {
        Bus::fake();

        $user = User::factory()->create();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => $visibility,
            'body' => 'old body',
        ]);

        Passport::actingAs($user);
        $response = $this->patchJson(route('posts.update', ['postId' => $post->id]), [
            'id' => $post->id,
            'visibility' => $visibility->value,
            'body' => 'new body',
        ]);

        $response->assertOk();

        Bus::assertNotDispatched(PushPostToMastodon::class);
        Bus::assertNotDispatched(PushDeleteToMastodon::class);
    }

    public function test_mass_edit_making_posts_private_sends_delete_activities(): void
    {
        Bus::fake();

        $user = User::factory()->create();
        $posts = Post::factory()->count(2)->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PUBLIC,
        ]);

        Passport::actingAs($user);
        $response = $this->postJson(route('api.posts.mass-edit'), [
            'postIds' => $posts->pluck('id')->toArray(),
            'visibility' => Visibility::PRIVATE->value,
        ]);

        $response->assertOk();

        foreach ($posts as $post) {
            Bus::assertDispatched(PushDeleteToMastodon::class, function (PushDeleteToMastodon $job) use ($post) {
                return $this->jobProperty($job, 'postId') === $post->id;
            });
        }
        Bus::assertNotDispatched(PushUpdateToMastodon::class);
        Bus::assertNotDispatched(PushPostToMastodon::class);
    }

    public function test_mass_edit_making_posts_public_sends_create_activities(): void
    {
        Bus::fake();

        $user = User::factory()->create();
        $posts = Post::factory()->count(2)->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PRIVATE,
        ]);

        Passport::actingAs($user);
        $response = $this->postJson(route('api.posts.mass-edit'), [
            'postIds' => $posts->pluck('id')->toArray(),
            'visibility' => Visibility::PUBLIC->value,
        ]);

        $response->assertOk();

        foreach ($posts as $post) {
            Bus::assertDispatched(PushPostToMastodon::class, function (PushPostToMastodon $job) use ($post) {
                return $this->jobProperty($job, 'postId') === $post->id;
            });
        }
        Bus::assertNotDispatched(PushUpdateToMastodon::class);
        Bus::assertNotDispatched(PushDeleteToMastodon::class);
    }
}
