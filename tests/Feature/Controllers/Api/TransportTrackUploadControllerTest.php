<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\Post;
use App\Models\TransportPost;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Passport\Passport;
use Tests\TestCase;

class TransportTrackUploadControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeTransportPost(User $user): Post
    {
        $trip = TransportTrip::factory()->create();
        $originStop = TransportTripStop::factory()->create(['transport_trip_id' => $trip->id, 'stop_sequence' => 1]);
        $destinationStop = TransportTripStop::factory()->create(['transport_trip_id' => $trip->id, 'stop_sequence' => 2]);

        $post = Post::factory()->create(['user_id' => $user->id]);

        TransportPost::factory()->create([
            'post_id' => $post->id,
            'transport_trip_id' => $trip->id,
            'origin_stop_id' => $originStop->id,
            'destination_stop_id' => $destinationStop->id,
            'manual_departure' => now(),
            'manual_arrival' => now()->addHour(),
        ]);

        return $post;
    }

    public function test_uploading_malformed_gpx_returns_422_with_message(): void
    {
        $user = User::factory()->create();
        $post = $this->makeTransportPost($user);

        Passport::actingAs($user);
        $file = UploadedFile::fake()->createWithContent('track.gpx', 'this is not xml at all <<<');

        $response = $this->postJson(route('posts.upload.transport-track', ['postId' => $post->id]), [
            'track' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Invalid GPX file']);
    }

    public function test_uploading_malformed_geojson_returns_422_with_message(): void
    {
        $user = User::factory()->create();
        $post = $this->makeTransportPost($user);

        Passport::actingAs($user);
        $file = UploadedFile::fake()->createWithContent('track.geojson', 'not valid json {{{');

        $response = $this->postJson(route('posts.upload.transport-track', ['postId' => $post->id]), [
            'track' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Invalid GeoJSON file']);
    }
}
