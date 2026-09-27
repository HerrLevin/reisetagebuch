<?php

namespace Tests\Feature\Controllers\Api;

use App\Enums\Visibility;
use App\Models\Post;
use App\Models\TransportPost;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class GuestMapAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeTransportPost(Visibility $visibility): TransportPost
    {
        $post = Post::factory()->create(['visibility' => $visibility->value]);
        $trip = TransportTrip::factory()->create();
        $origin = TransportTripStop::factory()->create(['transport_trip_id' => $trip->id, 'stop_sequence' => 1]);
        $destination = TransportTripStop::factory()->create(['transport_trip_id' => $trip->id, 'stop_sequence' => 2]);

        return TransportPost::factory()->create([
            'post_id' => $post->id,
            'transport_trip_id' => $trip->id,
            'origin_stop_id' => $origin->id,
            'destination_stop_id' => $destination->id,
        ]);
    }

    public function test_guest_can_fetch_linestring_without_authentication(): void
    {
        $transportPost = $this->makeTransportPost(Visibility::PUBLIC);

        $response = $this->getJson(route('posts.get.linestring', [
            'from' => $transportPost->origin_stop_id,
            'to' => $transportPost->destination_stop_id,
        ]));

        $response->assertOk();
    }

    public function test_guest_can_fetch_stopover_geometry_without_authentication(): void
    {
        $transportPost = $this->makeTransportPost(Visibility::PUBLIC);

        $response = $this->getJson(route('posts.get.stopovers', [
            'from' => $transportPost->origin_stop_id,
            'to' => $transportPost->destination_stop_id,
        ]));

        $response->assertOk();
    }

    #[TestWith([Visibility::PUBLIC])]
    #[TestWith([Visibility::UNLISTED])]
    public function test_guest_can_fetch_stopover_list_for_public_transport_post(Visibility $visibility): void
    {
        $transportPost = $this->makeTransportPost($visibility);

        $response = $this->getJson(route('posts.transport.stopovers.list', [
            'postId' => $transportPost->post_id,
        ]));

        $response->assertOk();
    }

    #[TestWith([Visibility::PRIVATE], 'private')]
    #[TestWith([Visibility::ONLY_AUTHENTICATED], 'only authenticated')]
    public function test_guest_cannot_fetch_stopover_list_for_private_transport_post(Visibility $visibility): void
    {
        $transportPost = $this->makeTransportPost($visibility);

        $response = $this->getJson(route('posts.transport.stopovers.list', [
            'postId' => $transportPost->post_id,
        ]));

        $response->assertForbidden();
    }

    public function test_guest_cannot_log_stopover_arrival(): void
    {
        $transportPost = $this->makeTransportPost(Visibility::PUBLIC);
        $stop = TransportTripStop::where('transport_trip_id', $transportPost->transport_trip_id)
            ->where('id', $transportPost->origin_stop_id)
            ->first();

        $response = $this->postJson(route('posts.transport.stopovers.arrival', [
            'postId' => $transportPost->post_id,
            'stopId' => $stop->id,
        ]), ['timestamp' => now()->toIso8601String()]);

        $response->assertUnauthorized();
    }
}
