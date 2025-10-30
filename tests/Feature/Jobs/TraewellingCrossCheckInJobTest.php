<?php

namespace Feature\Jobs;

use App\Jobs\TraewellingCrossCheckInJob;
use App\Models\Location;
use App\Models\LocationIdentifier;
use App\Models\Post;
use App\Models\SocialAccount;
use App\Models\TransportPost;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;

class TraewellingCrossCheckInJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Prevent actual logging
        Log::spy();
    }

    public function test_skips_if_no_traewelling_account()
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        // No SocialAccount created for user and provider 'traewelling'

        $job = Mockery::mock(TraewellingCrossCheckInJob::class, [$post->id])->makePartial();
        $job->shouldAllowMockingProtectedMethods();
        $job->shouldNotReceive('getAccessToken');

        $job->handle();
        Log::shouldHaveReceived('debug')->withArgs(function ($msg) {
            return str_contains($msg, 'Skipping Traewelling check-in');
        });
    }

    public function test_skips_if_already_has_traewelling_meta()
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        $post->metaInfos()->create(['key' => 'traewelling_trip_id', 'value' => '123']);
        // Create SocialAccount for user and provider 'traewelling'
        SocialAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'traewelling',
        ]);

        $job = Mockery::mock(TraewellingCrossCheckInJob::class, [$post->id])->makePartial();
        $job->shouldAllowMockingProtectedMethods();
        $job->shouldNotReceive('getAccessToken');

        $job->handle();
        Log::shouldHaveReceived('debug')->withArgs(function ($msg) {
            return str_contains($msg, 'Skipping Traewelling check-in');
        });
    }

    public function test_logs_error_on_unknown_provider()
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        $trip = TransportTrip::factory()->create(['provider' => 'nonexistent']);
        TransportPost::factory()->create(['post_id' => $post->id, 'transport_trip_id' => $trip->id]);
        // Create SocialAccount for user and provider 'traewelling'
        SocialAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'traewelling',
        ]);
        // Simulate transportPost relationship with unknown provider
        $transportPost = new \stdClass;
        $transportPost->transportTrip = (object) ['provider' => 'unknown'];
        $post->setRelation('transportPost', $transportPost);

        $job = Mockery::mock(TraewellingCrossCheckInJob::class, [$post->id])->makePartial();
        $job->shouldAllowMockingProtectedMethods();
        $job->shouldNotReceive('getAccessToken');

        $job->handle();
        Log::shouldHaveReceived('error')->withArgs(function ($msg) {
            return str_contains($msg, 'Unknown trip provider');
        });
    }

    public function test_crosspost_transitous_success()
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        $trip = TransportTrip::factory()->create(['provider' => 'transitous']);
        $transportPost = TransportPost::factory()->create([
            'post_id' => $post->id,
            'transport_trip_id' => $trip->id,
        ]);
        SocialAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'traewelling',
        ]);

        // Mock Guzzle Client
        $mockResponse = Mockery::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getBody->getContents')->andReturn(json_encode([
            'status' => ['id' => 'trwl_trip_id'],
        ]));
        $stationMockResponse = Mockery::mock(ResponseInterface::class);
        $stationMockResponse->shouldReceive('getBody->getContents')->andReturn(json_encode([
            'data' => [
                'id' => 'station_origin_id',
                'name' => 'Idk station name',
            ],
        ]));
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')->andReturn($mockResponse);
        $mockClient->shouldReceive('get')
            ->twice()
            ->andReturn($stationMockResponse);

        $job = new TraewellingCrossCheckInJob($post->id, $mockClient);
        $job->handle();

        $this->assertDatabaseHas('post_meta_infos', [
            'post_id' => $post->id,
            'key' => 'traewelling_trip_id',
            'value' => 'trwl_trip_id',
        ]);
        Log::shouldHaveReceived('debug')->withArgs(function ($msg) {
            return str_contains($msg, 'Created Traewelling check-in for post');
        });
    }

    public function test_crosspost_manual_trip_success()
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        $trip = TransportTrip::factory()->create(['provider' => 'reisetagebuch']);
        $origin = TransportTripStop::factory()->create(['transport_trip_id' => $trip->id]);
        $destination = TransportTripStop::factory()->create(['transport_trip_id' => $trip->id]);
        $transportPost = TransportPost::factory()->create([
            'post_id' => $post->id,
            'transport_trip_id' => $trip->id,
            'origin_stop_id' => $origin->id,
            'destination_stop_id' => $destination->id,
        ]);
        SocialAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'traewelling',
        ]);

        // Mock Guzzle Client for createTrip and checkin
        $mockResponseTrip = Mockery::mock(ResponseInterface::class);
        $mockResponseTrip->shouldReceive('getBody->getContents')->andReturn(json_encode([
            'data' => ['id' => 'trwl_trip_id'],
        ]));
        $mockResponseCheckin = Mockery::mock(ResponseInterface::class);
        $mockResponseCheckin->shouldReceive('getBody->getContents')->andReturn(json_encode([
            'status' => ['id' => 'trwl_trip_id'],
        ]));
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')
            ->withArgs(function ($url) {
                return $url === 'trains/trip';
            })
            ->andReturn($mockResponseTrip);
        $mockClient->shouldReceive('post')
            ->withArgs(function ($url) {
                return $url === 'trains/checkin';
            })
            ->andReturn($mockResponseCheckin);

        $stationMockResponse = Mockery::mock(ResponseInterface::class);
        $stationMockResponse->shouldReceive('getBody->getContents')->andReturn(json_encode([
            'data' => [
                'id' => 'station_origin_id',
                'name' => 'Idk station name',
            ],
        ]));
        $mockClient->shouldReceive('get')
            ->withArgs(function ($url) {
                return str_contains($url, 'trains/station/nearby');
            })
            ->twice()
            ->andReturn($stationMockResponse);

        $job = new TraewellingCrossCheckInJob($post->id, $mockClient);
        $job->handle();

        $this->assertDatabaseHas('post_meta_infos', [
            'post_id' => $post->id,
            'key' => 'traewelling_trip_id',
            'value' => 'trwl_trip_id',
        ]);
        Log::shouldHaveReceived('debug')->withArgs(function ($msg) {
            return str_contains($msg, 'Created Traewelling check-in for post');
        });
    }

    public function test_crosspost_transitous_missing_identifier_throws()
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        $trip = TransportTrip::factory()->create(['provider' => 'transitous']);
        TransportPost::factory()->create([
            'post_id' => $post->id,
            'transport_trip_id' => $trip->id,
        ]);
        SocialAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'traewelling',
        ]);

        // No Guzzle mock needed, but force the identifiers to be missing
        // Remove all identifiers from origin/destination locations
        $originStop = $post->transportPost->originStop;
        $destinationStop = $post->transportPost->destinationStop;
        $originStop->location->identifiers()->delete();
        $destinationStop->location->identifiers()->delete();

        $job = new TraewellingCrossCheckInJob($post->id);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Origin or destination not found');
        $job->handle();
        $this->assertDatabaseMissing('post_meta_infos', [
            'post_id' => $post->id,
            'key' => 'traewelling_trip_id',
        ]);
    }

    public function test_crosspost_manual_trip_missing_identifier_throws()
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        $trip = TransportTrip::factory()->create(['provider' => 'reisetagebuch']);
        TransportPost::factory()->create([
            'post_id' => $post->id,
            'transport_trip_id' => $trip->id,
        ]);
        SocialAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'traewelling',
        ]);
        $originStop = $post->transportPost->originStop;
        $destinationStop = $post->transportPost->destinationStop;
        $originStop->location->identifiers()->delete();
        $destinationStop->location->identifiers()->delete();

        $job = new TraewellingCrossCheckInJob($post->id);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Origin or destination departure board not found');
        $job->handle();
        $this->assertDatabaseMissing('post_meta_infos', [
            'post_id' => $post->id,
            'key' => 'traewelling_trip_id',
        ]);
    }

    public function test_existing_traewelling_id()
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        $trip = TransportTrip::factory()->create(['provider' => 'transitous']);
        $location = Location::factory()->create();
        LocationIdentifier::factory()->create([
            'location_id' => $location->id,
            'origin' => 'traewelling',
            'identifier' => 'station_origin_id',
        ]);
        $origin = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'location_id' => $location->id,
        ]);
        $destination = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'location_id' => $location->id,
        ]);
        TransportPost::factory()->create([
            'post_id' => $post->id,
            'transport_trip_id' => $trip->id,
            'origin_stop_id' => $origin->id,
            'destination_stop_id' => $destination->id,
        ]);
        SocialAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'traewelling',
        ]);

        // Mock Guzzle Client
        $mockResponse = Mockery::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getBody->getContents')->andReturn(json_encode([
            'status' => ['id' => 'trwl_trip_id'],
        ]));
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')->andReturn($mockResponse);
        $mockClient->shouldReceive('get')
            ->never();

        $job = new TraewellingCrossCheckInJob($post->id, $mockClient);
        $job->handle();

        $this->assertDatabaseHas('post_meta_infos', [
            'post_id' => $post->id,
            'key' => 'traewelling_trip_id',
            'value' => 'trwl_trip_id',
        ]);
        Log::shouldHaveReceived('debug')->withArgs(function ($msg) {
            return str_contains($msg, 'Created Traewelling check-in for post');
        });
    }

    public function test_existing_transitous_id()
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        $trip = TransportTrip::factory()->create(['provider' => 'transitous']);
        $location = Location::factory()->create();
        LocationIdentifier::factory()->create([
            'location_id' => $location->id,
            'origin' => 'motis',
            'identifier' => 'station_origin_id',
        ]);
        $origin = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'location_id' => $location->id,
        ]);
        $destination = TransportTripStop::factory()->create([
            'transport_trip_id' => $trip->id,
            'location_id' => $location->id,
        ]);
        TransportPost::factory()->create([
            'post_id' => $post->id,
            'transport_trip_id' => $trip->id,
            'origin_stop_id' => $origin->id,
            'destination_stop_id' => $destination->id,
        ]);
        SocialAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'traewelling',
        ]);

        // Mock Guzzle Client
        $mockResponse = Mockery::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getBody->getContents')->andReturn(json_encode([
            'status' => ['id' => 'trwl_trip_id'],
        ]));

        $stationMockResponse = Mockery::mock(ResponseInterface::class);
        $stationMockResponse->shouldReceive('getBody->getContents')->andReturn(json_encode([
            'data' => [[
                'id' => 'station_origin_id',
                'name' => 'Idk station name',
            ]],
        ]));
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('get')
            ->withArgs(function ($msg, $options) {
                return $msg == 'stations' && $options['query']['identifier'] === 'station_origin_id';
            })
            ->twice()
            ->andReturn($stationMockResponse);
        $mockClient->shouldReceive('post')->andReturn($mockResponse);

        $job = new TraewellingCrossCheckInJob($post->id, $mockClient);
        $job->handle();

        $this->assertDatabaseHas('post_meta_infos', [
            'post_id' => $post->id,
            'key' => 'traewelling_trip_id',
            'value' => 'trwl_trip_id',
        ]);
        Log::shouldHaveReceived('debug')->withArgs(function ($msg) {
            return str_contains($msg, 'Created Traewelling check-in for post');
        });
    }

    public function test_checkin_handles_409_conflict_and_retries()
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        $trip = TransportTrip::factory()->create(['provider' => 'transitous']);
        TransportPost::factory()->create([
            'post_id' => $post->id,
            'transport_trip_id' => $trip->id,
        ]);
        SocialAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'traewelling',
        ]);

        // Mock Guzzle Client to throw 409 on first call, then succeed
        $mockClient = Mockery::mock(Client::class);
        $mockResponse = Mockery::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getBody->getContents')->andReturn(json_encode([
            'status' => ['id' => 'trwl_trip_id'],
        ]));
        $mockClient->shouldReceive('post')
            ->once()
            ->andThrow(new ClientException('Conflict', Mockery::mock('Psr\Http\Message\RequestInterface'), Mockery::mock('Psr\Http\Message\ResponseInterface', function ($mock) {
                $mock->shouldReceive('getStatusCode')->andReturn(409);
            })));
        $mockClient->shouldReceive('post')
            ->once()
            ->andReturn($mockResponse);

        $stationMockResponse = Mockery::mock(ResponseInterface::class);
        $stationMockResponse->shouldReceive('getBody->getContents')->andReturn(json_encode([
            'data' => [
                'id' => 'station_origin_id',
                'name' => 'Idk station name',
            ],
        ]));
        $mockClient->shouldReceive('get')
            ->withArgs(function ($url) {
                return str_contains($url, 'trains/station/nearby');
            })
            ->twice()
            ->andReturn($stationMockResponse);

        $job = new TraewellingCrossCheckInJob($post->id, $mockClient);
        $job->handle();

        $this->assertDatabaseHas('post_meta_infos', [
            'post_id' => $post->id,
            'key' => 'traewelling_trip_id',
            'value' => 'trwl_trip_id',
        ]);
        Log::shouldHaveReceived('debug')->withArgs(function ($msg) {
            return str_contains($msg, 'Created Traewelling check-in for post');
        });
    }

    public function test_get_access_token_refreshes_if_expired()
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();
        $trip = TransportTrip::factory()->create(['provider' => 'transitous']);
        TransportPost::factory()->create([
            'post_id' => $post->id,
            'transport_trip_id' => $trip->id,
        ]);
        $socialAccount = SocialAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'traewelling',
            'access_token' => 'expired_token',
            'refresh_token' => 'refresh_token',
            'token_expires_at' => now()->subHour(),
            'updated_at' => now()->subDays(2),
        ]);

        // Mock Socialite to return a new token on refresh
        $mockToken = new \Laravel\Socialite\Two\User;
        $mockToken->token = 'new_access_token';
        $mockToken->refreshToken = 'refresh_token';
        $mockToken->expiresIn = 3600;
        Socialite::shouldReceive('driver')->with('traewelling')->andReturnSelf();
        Socialite::shouldReceive('refreshToken')->with('refresh_token')->andReturn($mockToken);

        // Mock Guzzle Client for checkin
        $mockResponse = Mockery::mock(ResponseInterface::class);
        $mockResponse->shouldReceive('getBody->getContents')->andReturn(json_encode([
            'status' => ['id' => 'trwl_trip_id'],
        ]));
        $mockClient = Mockery::mock(Client::class);
        $mockClient->shouldReceive('post')->andReturn($mockResponse);

        $stationMockResponse = Mockery::mock(ResponseInterface::class);
        $stationMockResponse->shouldReceive('getBody->getContents')->andReturn(json_encode([
            'data' => [
                'id' => 'station_origin_id',
                'name' => 'Idk station name',
            ],
        ]));
        $mockClient->shouldReceive('get')
            ->withArgs(function ($url) {
                return str_contains($url, 'trains/station/nearby');
            })
            ->twice()
            ->andReturn($stationMockResponse);

        $job = new TraewellingCrossCheckInJob($post->id, $mockClient);
        $job->handle();

        $this->assertDatabaseHas('post_meta_infos', [
            'post_id' => $post->id,
            'key' => 'traewelling_trip_id',
            'value' => 'trwl_trip_id',
        ]);
        $this->assertDatabaseHas('social_accounts', [
            'id' => $socialAccount->id,
            'access_token' => 'new_access_token',
        ]);
    }
}
