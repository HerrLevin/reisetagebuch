<?php

namespace Tests\Unit\Controllers\Backend;

use App\Http\Controllers\Backend\CalculateTransportStatsController;
use App\Models\Country;
use App\Models\Post;
use App\Models\TransportPost;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use App\Models\User;
use App\Repositories\CountryRepository;
use App\Repositories\PostRepository;
use App\Repositories\TransportTripRepository;
use App\Repositories\UserStatisticsRepository;
use Clickbar\Magellan\Data\Geometries\LineString;
use Clickbar\Magellan\Data\Geometries\Point;
use Database\Factories\CountryFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateTransportStatsControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeTransportPost(string $mode, ?LineString $userGeometry): Post
    {
        $trip = TransportTrip::factory()->create(['mode' => $mode]);
        $originStop = TransportTripStop::factory()->create(['transport_trip_id' => $trip->id, 'stop_sequence' => 1]);
        $destinationStop = TransportTripStop::factory()->create(['transport_trip_id' => $trip->id, 'stop_sequence' => 2]);

        $post = Post::factory()->create(['user_id' => User::factory()->create()->id]);

        TransportPost::factory()->create([
            'post_id' => $post->id,
            'transport_trip_id' => $trip->id,
            'origin_stop_id' => $originStop->id,
            'destination_stop_id' => $destinationStop->id,
            'manual_departure' => now(),
            'manual_arrival' => now()->addHour(),
            'user_geometry' => $userGeometry,
        ]);

        return $post;
    }

    public function test_stores_countries_the_route_crosses_for_a_non_flight_mode(): void
    {
        Country::factory()->create([
            'iso_a2' => 'ZZ',
            'geometry' => new CountryFactory()->squareAround(10, 10, 1),
        ]);

        // a straight line from (5,10) to (15,10) passes right through the ZZ square (lon 9-11, lat 9-11)
        $line = LineString::make([
            Point::make(5, 10, srid: 4326),
            Point::make(15, 10, srid: 4326),
        ], 4326);

        $post = $this->makeTransportPost('RAIL', $line);

        new CalculateTransportStatsController(
            app(PostRepository::class),
            app(TransportTripRepository::class),
            app(UserStatisticsRepository::class),
            app(CountryRepository::class),
        )->calculateStatsForPost($post->id, countryCalculation: true);

        $this->assertSame(['ZZ'], $post->transportPost->fresh()->transited_country_codes);
    }

    public function test_stores_no_transited_countries_for_airplane_mode_even_if_route_crosses_one(): void
    {
        Country::factory()->create([
            'iso_a2' => 'ZZ',
            'geometry' => new CountryFactory()->squareAround(10, 10, 1),
        ]);

        $line = LineString::make([
            Point::make(5, 10, srid: 4326),
            Point::make(15, 10, srid: 4326),
        ], 4326);

        $post = $this->makeTransportPost('AIRPLANE', $line);

        new CalculateTransportStatsController(
            app(PostRepository::class),
            app(TransportTripRepository::class),
            app(UserStatisticsRepository::class),
            app(CountryRepository::class),
        )->calculateStatsForPost($post->id);

        $this->assertSame([], $post->transportPost->fresh()->transited_country_codes);
    }
}
