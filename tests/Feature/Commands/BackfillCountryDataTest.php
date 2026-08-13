<?php

namespace Tests\Feature\Commands;

use App\Jobs\CalculateStatsForTransportPost;
use App\Models\Country;
use App\Models\Location;
use App\Models\Post;
use App\Models\TransportPost;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use Clickbar\Magellan\Data\Geometries\Point;
use Database\Factories\CountryFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BackfillCountryDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_backfills_country_code_on_existing_locations(): void
    {
        Country::factory()->create([
            'iso_a2' => 'ZZ',
            'geometry' => new CountryFactory()->squareAround(10, 10, 1),
        ]);

        $location = Location::factory()->create([
            'location' => Point::makeGeodetic(10, 10),
            'country_code' => null,
        ]);

        Artisan::call('app:backfill-country-data');

        $this->assertSame('ZZ', $location->fresh()->country_code);
    }

    public function test_dispatches_transited_country_calculation_for_existing_transport_posts(): void
    {
        Queue::fake();

        $trip = TransportTrip::factory()->create();
        $originStop = TransportTripStop::factory()->create(['transport_trip_id' => $trip->id, 'stop_sequence' => 1]);
        $destinationStop = TransportTripStop::factory()->create(['transport_trip_id' => $trip->id, 'stop_sequence' => 2]);
        $post = Post::factory()->create();
        TransportPost::factory()->create([
            'post_id' => $post->id,
            'transport_trip_id' => $trip->id,
            'origin_stop_id' => $originStop->id,
            'destination_stop_id' => $destinationStop->id,
            'transited_country_codes' => null,
        ]);

        Artisan::call('app:backfill-country-data');

        Queue::assertPushed(CalculateStatsForTransportPost::class, 1);
    }
}
