<?php

namespace Tests\Feature\Controllers\Api;

use App\Models\Post;
use App\Models\TransportPost;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ActiveTransportPostControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createActiveJourney(User $user): array
    {
        $trip = TransportTrip::factory()->create();

        $stops = [];
        $stops[0] = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'stop_sequence' => 0,
            'arrival_time' => null,
            'departure_time' => now()->subHours(2),
        ]);
        $stops[1] = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'stop_sequence' => 1,
            'arrival_time' => now()->subMinutes(90),
            'departure_time' => now()->subMinutes(80),
        ]);
        $stops[2] = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'stop_sequence' => 2,
            'arrival_time' => now()->subMinutes(30),
            'departure_time' => now()->subMinutes(20),
        ]);
        $stops[3] = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'stop_sequence' => 3,
            'arrival_time' => now()->addHours(2),
            'departure_time' => null,
        ]);

        $post = Post::factory()->create(['user_id' => $user->id]);
        $transportPost = TransportPost::factory()->create([
            'post_id' => $post->id,
            'transport_trip_id' => $trip->id,
            'origin_stop_id' => $stops[0]->id,
            'destination_stop_id' => $stops[3]->id,
            'manual_departure' => null,
            'manual_arrival' => null,
        ]);

        return [$post, $transportPost, $stops];
    }

    public function test_get_active_transport_post_returns_journey(): void
    {
        $user = User::factory()->create();
        [$post] = $this->createActiveJourney($user);

        Passport::actingAs($user);
        $response = $this->getJson(route('posts.transport.active'));

        $response->assertOk();
        $response->assertJsonPath('id', $post->id);
    }

    public function test_get_stopovers_for_transport_post_returns_journey_stopovers(): void
    {
        $user = User::factory()->create();
        [$post, , $stops] = $this->createActiveJourney($user);

        Passport::actingAs($user);
        $response = $this->getJson(route('posts.transport.stopovers.list', ['postId' => $post->id]));

        $response->assertOk();
        $response->assertJsonCount(4);
        $response->assertJsonPath('0.id', $stops[0]->id);
        $response->assertJsonPath('3.id', $stops[3]->id);
    }

    public function test_get_active_transport_post_returns_null_when_no_active_journey(): void
    {
        $user = User::factory()->create();

        Passport::actingAs($user);
        $response = $this->getJson(route('posts.transport.active'));

        $response->assertOk();
        $response->assertContent('null');
    }

    public function test_log_stopover_arrival_and_departure(): void
    {
        $user = User::factory()->create();
        [$post, , $stops] = $this->createActiveJourney($user);

        Passport::actingAs($user);

        $arrivalTimestamp = now()->subMinutes(5)->toIso8601String();
        $response = $this->postJson(route('posts.transport.stopovers.arrival', [
            'postId' => $post->id,
            'stopId' => $stops[2]->id,
        ]), ['timestamp' => $arrivalTimestamp]);
        $response->assertOk();
        $stopover = collect($response->json())->firstWhere('id', $stops[2]->id);
        $this->assertNotNull($stopover['manualArrivalTime']);
        $this->assertSame($arrivalTimestamp, Carbon::parse($stopover['manualArrivalTime'])->toIso8601String());
        $this->assertNull($stopover['manualDepartureTime']);

        $departureTimestamp = now()->subMinutes(2)->toIso8601String();
        $response = $this->postJson(route('posts.transport.stopovers.departure', [
            'postId' => $post->id,
            'stopId' => $stops[2]->id,
        ]), ['timestamp' => $departureTimestamp]);
        $response->assertOk();
        $stopover = collect($response->json())->firstWhere('id', $stops[2]->id);
        $this->assertNotNull($stopover['manualArrivalTime']);
        $this->assertNotNull($stopover['manualDepartureTime']);
        $this->assertSame($departureTimestamp, Carbon::parse($stopover['manualDepartureTime'])->toIso8601String());
    }

    public function test_log_departure_at_origin_stop_updates_post_manual_departure(): void
    {
        $user = User::factory()->create();
        [$post, , $stops] = $this->createActiveJourney($user);

        Passport::actingAs($user);

        $timestamp = now()->subMinutes(90)->toIso8601String();
        $response = $this->postJson(route('posts.transport.stopovers.departure', [
            'postId' => $post->id,
            'stopId' => $stops[0]->id,
        ]), ['timestamp' => $timestamp]);
        $response->assertOk();

        $response = $this->getJson(route('api.posts.show', ['post' => $post->id]));
        $response->assertOk();
        $this->assertSame($timestamp, Carbon::parse($response->json('manualDepartureTime'))->toIso8601String());
        $this->assertNull($response->json('manualArrivalTime'));
    }

    public function test_log_arrival_at_destination_stop_updates_post_manual_arrival(): void
    {
        $user = User::factory()->create();
        [$post, , $stops] = $this->createActiveJourney($user);

        Passport::actingAs($user);

        $timestamp = now()->addHours(2)->toIso8601String();
        $response = $this->postJson(route('posts.transport.stopovers.arrival', [
            'postId' => $post->id,
            'stopId' => $stops[3]->id,
        ]), ['timestamp' => $timestamp]);
        $response->assertOk();

        $response = $this->getJson(route('api.posts.show', ['post' => $post->id]));
        $response->assertOk();
        $this->assertSame($timestamp, Carbon::parse($response->json('manualArrivalTime'))->toIso8601String());
        $this->assertNull($response->json('manualDepartureTime'));
    }

    public function test_log_arrival_or_departure_at_intermediate_stop_does_not_update_post_manual_times(): void
    {
        $user = User::factory()->create();
        [$post, , $stops] = $this->createActiveJourney($user);

        Passport::actingAs($user);

        $this->postJson(route('posts.transport.stopovers.arrival', [
            'postId' => $post->id,
            'stopId' => $stops[2]->id,
        ]), ['timestamp' => now()->subMinutes(30)->toIso8601String()])->assertOk();

        $this->postJson(route('posts.transport.stopovers.departure', [
            'postId' => $post->id,
            'stopId' => $stops[2]->id,
        ]), ['timestamp' => now()->subMinutes(20)->toIso8601String()])->assertOk();

        $response = $this->getJson(route('api.posts.show', ['post' => $post->id]));
        $response->assertOk();
        $this->assertNull($response->json('manualDepartureTime'));
        $this->assertNull($response->json('manualArrivalTime'));
    }

    public function test_clear_stopover_arrival_and_departure(): void
    {
        $user = User::factory()->create();
        [$post, , $stops] = $this->createActiveJourney($user);

        Passport::actingAs($user);

        $this->postJson(route('posts.transport.stopovers.arrival', [
            'postId' => $post->id,
            'stopId' => $stops[2]->id,
        ]), ['timestamp' => now()->subMinutes(30)->toIso8601String()])->assertOk();
        $this->postJson(route('posts.transport.stopovers.departure', [
            'postId' => $post->id,
            'stopId' => $stops[2]->id,
        ]), ['timestamp' => now()->subMinutes(20)->toIso8601String()])->assertOk();

        $response = $this->deleteJson(route('posts.transport.stopovers.arrival.clear', [
            'postId' => $post->id,
            'stopId' => $stops[2]->id,
        ]));
        $response->assertOk();
        $stopover = collect($response->json())->firstWhere('id', $stops[2]->id);
        $this->assertNull($stopover['manualArrivalTime']);
        $this->assertNotNull($stopover['manualDepartureTime']);

        $response = $this->deleteJson(route('posts.transport.stopovers.departure.clear', [
            'postId' => $post->id,
            'stopId' => $stops[2]->id,
        ]));
        $response->assertOk();
        $stopover = collect($response->json())->firstWhere('id', $stops[2]->id);
        $this->assertNull($stopover['manualArrivalTime']);
        $this->assertNull($stopover['manualDepartureTime']);
    }

    public function test_clear_stopover_arrival_on_stop_with_no_logged_time_is_a_no_op(): void
    {
        $user = User::factory()->create();
        [$post, , $stops] = $this->createActiveJourney($user);

        Passport::actingAs($user);

        $response = $this->deleteJson(route('posts.transport.stopovers.arrival.clear', [
            'postId' => $post->id,
            'stopId' => $stops[2]->id,
        ]));
        $response->assertOk();
        $stopover = collect($response->json())->firstWhere('id', $stops[2]->id);
        $this->assertNull($stopover['manualArrivalTime']);
    }

    public function test_clear_departure_at_origin_stop_clears_post_manual_departure(): void
    {
        $user = User::factory()->create();
        [$post, , $stops] = $this->createActiveJourney($user);

        Passport::actingAs($user);

        $this->postJson(route('posts.transport.stopovers.departure', [
            'postId' => $post->id,
            'stopId' => $stops[0]->id,
        ]), ['timestamp' => now()->subMinutes(90)->toIso8601String()])->assertOk();

        $this->deleteJson(route('posts.transport.stopovers.departure.clear', [
            'postId' => $post->id,
            'stopId' => $stops[0]->id,
        ]))->assertOk();

        $response = $this->getJson(route('api.posts.show', ['post' => $post->id]));
        $response->assertOk();
        $this->assertNull($response->json('manualDepartureTime'));
    }

    public function test_clear_arrival_at_destination_stop_clears_post_manual_arrival(): void
    {
        $user = User::factory()->create();
        [$post, , $stops] = $this->createActiveJourney($user);

        Passport::actingAs($user);

        $this->postJson(route('posts.transport.stopovers.arrival', [
            'postId' => $post->id,
            'stopId' => $stops[3]->id,
        ]), ['timestamp' => now()->addHours(2)->toIso8601String()])->assertOk();

        $this->deleteJson(route('posts.transport.stopovers.arrival.clear', [
            'postId' => $post->id,
            'stopId' => $stops[3]->id,
        ]))->assertOk();

        $response = $this->getJson(route('api.posts.show', ['post' => $post->id]));
        $response->assertOk();
        $this->assertNull($response->json('manualArrivalTime'));
    }

    public function test_update_transport_times_updates_origin_and_destination_stopover_logs(): void
    {
        $user = User::factory()->create();
        [$post, , $stops] = $this->createActiveJourney($user);

        Passport::actingAs($user);

        $departureTimestamp = now()->subHours(2)->toIso8601String();
        $arrivalTimestamp = now()->addHours(2)->toIso8601String();
        $response = $this->putJson(route('posts.update.transport-times', ['postId' => $post->id]), [
            'manualDepartureTime' => $departureTimestamp,
            'manualArrivalTime' => $arrivalTimestamp,
        ]);
        $response->assertOk();

        $response = $this->getJson(route('posts.transport.stopovers.list', ['postId' => $post->id]));
        $response->assertOk();
        $stopoverList = collect($response->json());
        $this->assertSame(
            $departureTimestamp,
            Carbon::parse($stopoverList->firstWhere('id', $stops[0]->id)['manualDepartureTime'])->toIso8601String(),
        );
        $this->assertSame(
            $arrivalTimestamp,
            Carbon::parse($stopoverList->firstWhere('id', $stops[3]->id)['manualArrivalTime'])->toIso8601String(),
        );
    }

    public function test_clearing_manual_transport_times_clears_origin_and_destination_stopover_logs(): void
    {
        $user = User::factory()->create();
        [$post, , $stops] = $this->createActiveJourney($user);

        Passport::actingAs($user);

        $this->putJson(route('posts.update.transport-times', ['postId' => $post->id]), [
            'manualDepartureTime' => now()->subHours(2)->toIso8601String(),
            'manualArrivalTime' => now()->addHours(2)->toIso8601String(),
        ])->assertOk();

        $response = $this->putJson(route('posts.update.transport-times', ['postId' => $post->id]), [
            'manualDepartureTime' => null,
            'manualArrivalTime' => null,
        ]);
        $response->assertOk();

        $response = $this->getJson(route('posts.transport.stopovers.list', ['postId' => $post->id]));
        $response->assertOk();
        $stopoverList = collect($response->json());
        $this->assertNull($stopoverList->firstWhere('id', $stops[0]->id)['manualDepartureTime']);
        $this->assertNull($stopoverList->firstWhere('id', $stops[3]->id)['manualArrivalTime']);
    }

    public function test_log_stopover_arrival_normalizes_non_utc_offset_timestamp_to_utc(): void
    {
        $user = User::factory()->create();
        [$post, , $stops] = $this->createActiveJourney($user);

        Passport::actingAs($user);

        // 12:00 in +02:00 is 10:00 UTC
        $response = $this->postJson(route('posts.transport.stopovers.arrival', [
            'postId' => $post->id,
            'stopId' => $stops[2]->id,
        ]), ['timestamp' => '2024-06-01T12:00:00.000+02:00']);
        $response->assertOk();

        $stopover = collect($response->json())->firstWhere('id', $stops[2]->id);
        $this->assertSame('2024-06-01T10:00:00+00:00', $stopover['manualArrivalTime']);
    }

    public function test_update_transport_times_normalizes_non_utc_offset_and_cascades_to_stopover_log(): void
    {
        $user = User::factory()->create();
        [$post, , $stops] = $this->createActiveJourney($user);

        Passport::actingAs($user);

        // 12:00 in +02:00 is 10:00 UTC, 22:00 in +02:00 is 20:00 UTC
        $response = $this->putJson(route('posts.update.transport-times', ['postId' => $post->id]), [
            'manualDepartureTime' => '2024-06-01T12:00:00.000+02:00',
            'manualArrivalTime' => '2024-06-01T22:00:00.000+02:00',
        ]);
        $response->assertOk();
        $response->assertJsonPath('manualDepartureTime', '2024-06-01T10:00:00+00:00');
        $response->assertJsonPath('manualArrivalTime', '2024-06-01T20:00:00+00:00');

        $response = $this->getJson(route('posts.transport.stopovers.list', ['postId' => $post->id]));
        $response->assertOk();
        $stopoverList = collect($response->json());
        $this->assertSame(
            '2024-06-01T10:00:00+00:00',
            $stopoverList->firstWhere('id', $stops[0]->id)['manualDepartureTime'],
        );
        $this->assertSame(
            '2024-06-01T20:00:00+00:00',
            $stopoverList->firstWhere('id', $stops[3]->id)['manualArrivalTime'],
        );
    }

    public function test_clear_stopover_forbidden_for_other_user(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        [$post, , $stops] = $this->createActiveJourney($owner);

        Passport::actingAs($otherUser);
        $response = $this->deleteJson(route('posts.transport.stopovers.arrival.clear', [
            'postId' => $post->id,
            'stopId' => $stops[1]->id,
        ]));

        $response->assertForbidden();
    }

    public function test_log_stopover_forbidden_for_other_user(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        [$post, , $stops] = $this->createActiveJourney($owner);

        Passport::actingAs($otherUser);
        $response = $this->postJson(route('posts.transport.stopovers.arrival', [
            'postId' => $post->id,
            'stopId' => $stops[1]->id,
        ]), ['timestamp' => now()->toIso8601String()]);

        $response->assertForbidden();
    }
}
