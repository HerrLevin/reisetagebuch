<?php

namespace Tests\Repository;

use App\Enums\Visibility;
use App\Http\Resources\PostTypes\TransportPost as TransportPostDto;
use App\Models\TransportPost;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use App\Models\User;
use App\Repositories\PostRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransportPostPublishedAtTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_at_uses_realtime_departure_including_delay(): void
    {
        $user = User::factory()->create();
        $trip = TransportTrip::factory()->create();

        $originStop = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'stop_sequence' => 0,
            'departure_time' => Carbon::parse('2026-01-01 10:00:00'),
            'departure_delay' => 1800, // 30 minutes late
        ]);
        $destinationStop = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'stop_sequence' => 1,
        ]);

        $repo = new PostRepository;
        $post = $repo->storeTransport(
            user: $user,
            transportTrip: $trip,
            originStop: $originStop,
            destinationStop: $destinationStop,
            visibility: Visibility::PUBLIC,
        );

        $this->assertInstanceOf(TransportPostDto::class, $post);
        // Real departure is 10:30 (scheduled + 30min delay), published_at is 10 min before that.
        $this->assertSame('2026-01-01T10:20:00+00:00', Carbon::parse($post->publishedAt)->toIso8601String());
    }

    public function test_published_at_falls_back_to_realtime_arrival_when_no_departure(): void
    {
        $user = User::factory()->create();
        $trip = TransportTrip::factory()->create();

        $originStop = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'stop_sequence' => 0,
            'departure_time' => null,
            'departure_delay' => null,
            'arrival_time' => Carbon::parse('2026-01-01 10:00:00'),
            'arrival_delay' => 300,
        ]);
        $destinationStop = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'stop_sequence' => 1,
        ]);

        $repo = new PostRepository;
        $post = $repo->storeTransport(
            user: $user,
            transportTrip: $trip,
            originStop: $originStop,
            destinationStop: $destinationStop,
            visibility: Visibility::PUBLIC,
        );

        $this->assertSame('2026-01-01T09:55:00+00:00', Carbon::parse($post->publishedAt)->toIso8601String());
    }

    public function test_manual_departure_correction_updates_published_at(): void
    {
        $user = User::factory()->create();
        $trip = TransportTrip::factory()->create();

        $originStop = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'stop_sequence' => 0,
            'departure_time' => Carbon::parse('2026-01-01 10:00:00'),
            'departure_delay' => 0,
        ]);
        $destinationStop = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'stop_sequence' => 1,
        ]);

        $repo = new PostRepository;
        $post = $repo->storeTransport(
            user: $user,
            transportTrip: $trip,
            originStop: $originStop,
            destinationStop: $destinationStop,
            visibility: Visibility::PUBLIC,
        );

        $repo->updateTransportTimes(
            transportPost: $post,
            manualDepartureTime: '2026-01-01T11:15:00Z',
            updateDeparture: true,
            manualArrivalTime: null,
            updateArrival: false,
        );

        $updated = TransportPost::where('post_id', $post->id)->firstOrFail();
        $this->assertSame('2026-01-01T11:05:00+00:00', Carbon::parse($updated->post->published_at)->toIso8601String());
    }

    public function test_clearing_manual_departure_reverts_published_at_to_schedule(): void
    {
        $user = User::factory()->create();
        $trip = TransportTrip::factory()->create();

        $originStop = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'stop_sequence' => 0,
            'departure_time' => Carbon::parse('2026-01-01 10:00:00'),
            'departure_delay' => 0,
        ]);
        $destinationStop = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'stop_sequence' => 1,
        ]);

        $repo = new PostRepository;
        $post = $repo->storeTransport(
            user: $user,
            transportTrip: $trip,
            originStop: $originStop,
            destinationStop: $destinationStop,
            visibility: Visibility::PUBLIC,
        );

        $updatedOnce = $repo->updateTransportTimes(
            transportPost: $post,
            manualDepartureTime: '2026-01-01T11:15:00Z',
            updateDeparture: true,
            manualArrivalTime: null,
            updateArrival: false,
        );

        $repo->updateTransportTimes(
            transportPost: $updatedOnce,
            manualDepartureTime: null,
            updateDeparture: true,
            manualArrivalTime: null,
            updateArrival: false,
        );

        $updated = TransportPost::where('post_id', $post->id)->firstOrFail();
        $this->assertSame('2026-01-01T09:50:00+00:00', Carbon::parse($updated->post->published_at)->toIso8601String());
    }
}
