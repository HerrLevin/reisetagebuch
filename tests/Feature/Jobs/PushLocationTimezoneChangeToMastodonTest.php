<?php

namespace Tests\Feature\Jobs;

use App\Enums\Visibility;
use App\Jobs\ActivityPub\PushLocationTimezoneChangeToMastodon;
use App\Jobs\ActivityPub\PushUpdateToMastodon;
use App\Models\Location;
use App\Models\Post;
use App\Models\TransportPost;
use App\Models\TransportTripStop;
use App\Repositories\PostRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use ReflectionProperty;
use Tests\TestCase;

class PushLocationTimezoneChangeToMastodonTest extends TestCase
{
    use RefreshDatabase;

    private function jobProperty(object $job, string $name): mixed
    {
        return new ReflectionProperty($job, $name)->getValue($job);
    }

    public function test_repository_returns_public_posts_using_the_location_as_origin_or_destination(): void
    {
        $location = Location::factory()->create();

        $originPost = $this->makeTransportPostAtLocation($location, asOrigin: true, visibility: Visibility::PUBLIC);
        $destinationPost = $this->makeTransportPostAtLocation($location, asOrigin: false, visibility: Visibility::PUBLIC);
        $unrelatedPost = TransportPost::factory()->create()->post;

        $result = app(PostRepository::class)->getActivePostIdsForLocation($location->id);

        $this->assertContains($originPost->id, $result);
        $this->assertContains($destinationPost->id, $result);
        $this->assertNotContains($unrelatedPost->id, $result);
    }

    public function test_repository_excludes_non_public_posts(): void
    {
        $location = Location::factory()->create();
        $privatePost = $this->makeTransportPostAtLocation($location, asOrigin: true, visibility: Visibility::PRIVATE);

        $result = app(PostRepository::class)->getActivePostIdsForLocation($location->id);

        $this->assertNotContains($privatePost->id, $result);
    }

    public function test_job_dispatches_mastodon_update_for_every_active_post_at_the_location(): void
    {
        $location = Location::factory()->create();
        $post = $this->makeTransportPostAtLocation($location, asOrigin: true, visibility: Visibility::PUBLIC);

        Bus::fake();

        (new PushLocationTimezoneChangeToMastodon($location->id))->handle(app(PostRepository::class));

        Bus::assertDispatched(PushUpdateToMastodon::class, function (PushUpdateToMastodon $job) use ($post) {
            return $this->jobProperty($job, 'postId') === $post->id;
        });
    }

    private function makeTransportPostAtLocation(Location $location, bool $asOrigin, Visibility $visibility): Post
    {
        $post = Post::factory()->create(['visibility' => $visibility->value]);
        $stop = TransportTripStop::factory()->create(['location_id' => $location->id]);

        TransportPost::factory()->create([
            'post_id' => $post->id,
            'origin_stop_id' => $asOrigin ? $stop->id : TransportTripStop::factory()->create()->id,
            'destination_stop_id' => $asOrigin ? TransportTripStop::factory()->create()->id : $stop->id,
        ]);

        return $post;
    }
}
