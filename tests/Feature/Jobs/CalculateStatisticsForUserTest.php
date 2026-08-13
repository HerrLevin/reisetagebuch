<?php

namespace Tests\Feature\Jobs;

use App\Jobs\CalculateStatisticsForUser;
use App\Models\Location;
use App\Models\LocationPost;
use App\Models\Post;
use App\Models\TransportPost;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateStatisticsForUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_visited_countries_count_is_populated_and_not_double_counted(): void
    {
        // UserFactory already creates a statistics row for every user
        $user = User::factory()->create();

        // location post in AA
        $location = Location::factory()->create(['country_code' => 'AA']);
        $locationPost = Post::factory()->create(['user_id' => $user->id]);
        LocationPost::create(['post_id' => $locationPost->id, 'location_id' => $location->id]);

        // transport post between BB and AA (AA already counted via the location post above)
        $trip = TransportTrip::factory()->create();
        $originLocation = Location::factory()->create(['country_code' => 'BB']);
        $destinationLocation = Location::factory()->create(['country_code' => 'AA']);
        $originStop = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'location_id' => $originLocation->id,
            'stop_sequence' => 1,
        ]);
        $destinationStop = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'location_id' => $destinationLocation->id,
            'stop_sequence' => 2,
        ]);
        $transportPostPost = Post::factory()->create(['user_id' => $user->id]);
        TransportPost::factory()->create([
            'post_id' => $transportPostPost->id,
            'transport_trip_id' => $trip->id,
            'origin_stop_id' => $originStop->id,
            'destination_stop_id' => $destinationStop->id,
        ]);

        new CalculateStatisticsForUser($user->id)->handle();

        $this->assertSame(2, $user->statistics->fresh()->visited_countries_count);
    }
}
