<?php

namespace App\Jobs;

use App\Models\LocationIdentifier;
use App\Models\Post;
use App\Models\PostMetaInfo;
use App\Models\SocialAccount;
use App\Models\TransportTripStop;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\Token;
use Log;
use Throwable;

class TraewellingCrossCheckInJob implements ShouldQueue
{
    use Queueable;

    private string $postId;

    private string $accessToken;

    public function __construct(string $postId)
    {
        $this->postId = $postId;
    }

    /**
     * @throws GuzzleException
     */
    public function handle(): void
    {
        $post = Post::with(['transportPost.originStop.location.identifiers', 'transportPost.destinationStop.location.identifiers', 'transportPost.transportTrip'])->find($this->postId);
        $hasTrwlMeta = $post?->metaInfos->where('key', 'traewelling_trip_id')->count() > 0;

        if ($hasTrwlMeta || $post?->transportPost?->transportTrip?->provider !== 'transitous') {
            return;
        }
        $this->getAccessToken($post);

        $originStop = $post->transportPost->originStop;
        $destinationStop = $post->transportPost->destinationStop;

        $trwlOriginIdentifier = $this->getOriginFromDepartureBoard($post);
        if (! $trwlOriginIdentifier) {
            throw new Exception('Origin departure board not found');
        }

        $trwlDestinationIdentifier = $this->getDestinationFromStopovers($post, $trwlOriginIdentifier['id']);
        if (! $trwlDestinationIdentifier) {
            throw new Exception('Destination stop not found in stopovers');
        }

        $trwlOrigin = LocationIdentifier::updateOrCreate(
            ['origin' => 'traewelling', 'type' => 'stop', 'location_id' => $originStop->location->id],
            ['identifier' => $trwlOriginIdentifier['id'], 'name' => $trwlOriginIdentifier['name']]
        );
        $trwlDestination = LocationIdentifier::updateOrCreate(
            ['origin' => 'traewelling', 'type' => 'stop', 'location_id' => $destinationStop->location->id],
            ['identifier' => $trwlDestinationIdentifier['id'], 'name' => $trwlDestinationIdentifier['name']]
        );

        // post to traewelling
        $data = $this->checkin($post, $trwlOrigin, $trwlDestination);
        if (isset($data['error'])) {
            throw new Exception('Traewelling API error: '.$data['error']);
        }

        $meta = new PostMetaInfo;
        $meta->post_id = $post->id;
        $meta->key = 'traewelling_trip_id';
        $meta->value = $data['data']['id'] ?? '';
        $meta->save();
    }

    public function getAccessToken(Post $post): void
    {
        $account = SocialAccount::whereUserId($post->user->id)->whereProvider('traewelling')->first();

        // if traewelling token is expired, refresh it
        if ($account && $account->token_expires_at && $account->token_expires_at->isPast()) {
            /** @var Token $tokens */
            $tokens = Socialite::driver('traewelling')->refreshToken($account->refresh_token);
            $account->update([
                'access_token' => $tokens->token,
                'refresh_token' => $tokens->refreshToken,
                'token_expires_at' => now()->addSeconds($tokens->expiresIn),
            ]);
        }
        $this->accessToken = $account?->access_token ?? '';
    }

    private function getClient(): Client
    {
        $baseUrl = config('services.traewelling.base_uri').'/api/v1/';

        return new Client([
            'base_uri' => $baseUrl,
            'headers' => [
                'Authorization' => 'Bearer '.$this->accessToken,
                'Accept' => 'application/json',
            ],
        ]);
    }

    private function getTrwlStation(TransportTripStop $originStop): ?array
    {
        // get first entry from /stations?query=originStop->location->name with guzzle
        $response = $this->getClient()->get('stations', [
            'query' => [
                'query' => $originStop->location->name,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        $stations = $data['data'] ?? [];
        foreach ($stations as $station) {
            $cityName = '';
            $stationName = $station['name'];
            foreach ($station['areas'] as $area) {
                if ($area['default']) {
                    $cityName = $area['name'];
                    break;
                }
            }

            $stationNameWithoutCity = str_replace([$cityName, ',', ' '], '', $stationName);
            $originStationNameWithoutCity = str_replace([$cityName, ',', ' '], '', $originStop->location->name);

            similar_text($originStationNameWithoutCity, $stationNameWithoutCity, $percentWithoutCity);
            similar_text($stationName, $originStop->location->name, $percent);
            if ($percent > 80 || $percentWithoutCity > 80) {
                return $station;
            }
        }

        return null;
    }

    private function getDestinationFromStopovers(Post $post, string $startId): ?array
    {
        $tripId = $post->transportPost->transportTrip->foreign_trip_id;
        $client = $this->getClient();
        $response = $client->get('trains/trip', [
            'query' => [
                'hafasTripId' => $tripId,
                'lineName' => $post->transportPost->transportTrip->line_name,
                'start' => $startId,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        $trip = $data['data'] ?? [];

        $maybeDestinationId = $this->getTrwlStation($post->transportPost->destinationStop) ?? ['id' => null, 'name' => null];
        foreach ($trip['stopovers'] as $stop) {
            if ($stop['id'] === $maybeDestinationId['id']) {
                return $stop;
            }
        }

        $locationName = $post->transportPost->destinationStop->location->name;
        $destinationStopNameWithoutCity = explode(',', $locationName)[0];
        foreach ($trip['stopovers'] as $stop) {
            $stopName = $stop['name'];

            // delete everything after the first comma in the stop name
            $stopNameWithoutCity = explode(',', $stopName)[0];

            similar_text($destinationStopNameWithoutCity, $stopNameWithoutCity, $percentWithoutCity);
            similar_text($stopName, $locationName, $percent);
            if ($percent > 80 || $percentWithoutCity > 80) {
                return $stop;
            }
        }

        return null;
    }

    private function getOriginFromDepartureBoard(Post $post): ?array
    {
        $origin = $post->transportPost->originStop;
        $trwlOrigin = $this->getTrwlStation($origin);
        $client = $this->getClient();
        $response = $client->get('station/'.$trwlOrigin['id'].'/departures', [
            'query' => [
                'when' => $post->transportPost->originStop->departure_time?->toIso8601String(),
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        $departures = $data['data'] ?? [];
        foreach ($departures as $departure) {
            if ($departure['tripId'] === $post->transportPost->transportTrip->foreign_trip_id) {
                return $departure['stop'];
            }
        }

        return null;
    }

    /**
     * @throws GuzzleException
     * @throws Throwable
     */
    private function checkin(Post $post, LocationIdentifier $trwlOrigin, LocationIdentifier $trwlDestination, bool $force = false): mixed
    {
        $client = $this->getClient();
        $body = [
            'body' => $post->body,
            'start' => $trwlOrigin->identifier,
            'destination' => $trwlDestination->identifier,
            'tripId' => $post->transportPost->transportTrip->foreign_trip_id,
            'departure' => $post->transportPost->originStop->departure_time?->toIso8601String(),
            'arrival' => $post->transportPost->destinationStop->arrival_time?->toIso8601String(),
            'lineName' => $post->transportPost->transportTrip->line_name,
            'force' => $force,
        ];
        try {
            Log::info('Traewelling API checkin request', [
                'post_id' => $post->id,
                'body' => $body,
            ]);
            $response = $client->post('trains/checkin', ['json' => $body]);
        } catch (Throwable $e) {
            if ($e->getCode() === 409 && ! $force) {
                return $this->checkin($post, $trwlOrigin, $trwlDestination, true);
            } else {
                throw $e;
            }
        }

        return json_decode($response->getBody()->getContents(), true);
    }
}
