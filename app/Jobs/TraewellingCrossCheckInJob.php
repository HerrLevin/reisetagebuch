<?php

namespace App\Jobs;

use App\Models\LocationIdentifier;
use App\Models\Post;
use App\Models\PostMetaInfo;
use App\Models\SocialAccount;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\Token;
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
        $isTrwlUser = SocialAccount::whereUserId($post?->user->id)->whereProvider('traewelling')->exists();

        if (! $isTrwlUser || $hasTrwlMeta || $post?->transportPost?->transportTrip?->provider !== 'transitous') {
            Log::debug('Skipping Traewelling check-in for post '.$this->postId);

            return;
        }
        $this->getAccessToken($post);

        $originStop = $post->transportPost->originStop;
        $destinationStop = $post->transportPost->destinationStop;

        // todo: get from database
        $trwlOriginIdentifier = $this->getTrwlStationFromIdentifier($originStop->location->identifiers->firstWhere('origin', 'motis')?->identifier ?? '');
        $trwlDestinationIdentifier = $this->getTrwlStationFromIdentifier($destinationStop->location->identifiers->firstWhere('origin', 'motis')?->identifier ?? '');
        if (! $trwlOriginIdentifier || ! $trwlDestinationIdentifier) {
            throw new Exception('Origin departure board not found');
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

    private function getTrwlStationFromIdentifier(string $identifier): ?array
    {
        $client = $this->getClient();
        $response = $client->get('stations', [
            'query' => [
                'identifier' => $identifier,
                'identifier_provider' => 'transitous',
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['data'][0] ?? null;
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
            Log::debug('Traewelling API checkin request', [
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
