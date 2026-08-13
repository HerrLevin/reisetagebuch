<?php

namespace Tests\Repository;

use App\Models\Location;
use App\Models\LocationPost;
use App\Models\Post;
use App\Models\TransportPost;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use App\Models\User;
use App\Repositories\CountryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private function makeTransportPost(string $userId, string $originIso, string $destinationIso, string $mode, array $transitedCountryCodes = []): TransportPost
    {
        $trip = TransportTrip::factory()->create(['mode' => $mode]);

        $originLocation = Location::factory()->create(['country_code' => $originIso]);
        $destinationLocation = Location::factory()->create(['country_code' => $destinationIso]);

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

        $post = Post::factory()->create(['user_id' => $userId]);

        return TransportPost::factory()->create([
            'post_id' => $post->id,
            'transport_trip_id' => $trip->id,
            'origin_stop_id' => $originStop->id,
            'destination_stop_id' => $destinationStop->id,
            'transited_country_codes' => $transitedCountryCodes,
        ]);
    }

    public function test_location_post_counts_as_visited(): void
    {
        $user = User::factory()->create();
        $location = Location::factory()->create(['country_code' => 'AA']);
        $post = Post::factory()->create(['user_id' => $user->id]);
        LocationPost::create(['post_id' => $post->id, 'location_id' => $location->id]);

        $repository = new CountryRepository;

        $this->assertEqualsCanonicalizing(['AA'], $repository->getVisitedCountryCodes($user->id));
    }

    public function test_transport_post_endpoints_count_as_visited_even_without_a_location_post(): void
    {
        $user = User::factory()->create();
        $this->makeTransportPost($user->id, 'BB', 'CC', 'RAIL');

        $repository = new CountryRepository;

        $this->assertEqualsCanonicalizing(['BB', 'CC'], $repository->getVisitedCountryCodes($user->id));
    }

    public function test_transited_only_country_is_reported_separately_from_visited(): void
    {
        $user = User::factory()->create();
        $this->makeTransportPost($user->id, 'BB', 'CC', 'RAIL', ['BB', 'DD', 'CC']);

        $repository = new CountryRepository;
        $visited = $repository->getVisitedCountryCodes($user->id);
        $transitedOnly = $repository->getTransitedOnlyCountryCodes($user->id, $visited);

        $this->assertEqualsCanonicalizing(['BB', 'CC'], $visited);
        $this->assertEqualsCanonicalizing(['DD'], $transitedOnly);
    }

    public function test_country_visited_via_one_post_is_not_duplicated_into_transited_only(): void
    {
        $user = User::factory()->create();
        $location = Location::factory()->create(['country_code' => 'EE']);
        $post = Post::factory()->create(['user_id' => $user->id]);
        LocationPost::create(['post_id' => $post->id, 'location_id' => $location->id]);

        $this->makeTransportPost($user->id, 'FF', 'GG', 'RAIL', ['EE']);

        $repository = new CountryRepository;
        $visited = $repository->getVisitedCountryCodes($user->id);
        $transitedOnly = $repository->getTransitedOnlyCountryCodes($user->id, $visited);

        $this->assertEqualsCanonicalizing(['EE', 'FF', 'GG'], $visited);
        $this->assertEqualsCanonicalizing([], $transitedOnly);
    }

    public function test_does_not_include_another_users_countries(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $location = Location::factory()->create(['country_code' => 'HH']);
        $post = Post::factory()->create(['user_id' => $otherUser->id]);
        LocationPost::create(['post_id' => $post->id, 'location_id' => $location->id]);

        $repository = new CountryRepository;

        $this->assertSame([], $repository->getVisitedCountryCodes($user->id));
    }
}
