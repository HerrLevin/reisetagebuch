<?php

namespace App\Services;

use App\Models\LocationIdentifier;
use App\Models\SocialAccount;
use Clickbar\Magellan\Data\Geometries\Point;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\Token;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class TraewellingRequestService
{
    private ?Client $client;

    private string $accessToken;

    public function __construct(?Client $client = null)
    {
        $this->client = $client;
    }

    private function getClient(): Client
    {
        if ($this->client) {
            return $this->client;
        }
        $baseUrl = config('services.traewelling.base_uri').'/api/v1/';

        return new Client([
            'base_uri' => $baseUrl,
            'headers' => [
                'Authorization' => 'Bearer '.$this->accessToken,
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function getAccessToken(string $userId): void
    {
        $account = SocialAccount::whereUserId($userId)->whereProvider('traewelling')->first();

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

    public function getTrwlStationFromLocation(Point $point): ?array
    {
        $client = $this->getClient();
        try {
            $response = $client->get('trains/station/nearby', [
                'query' => [
                    'latitude' => $point->getY(),
                    'longitude' => $point->getX(),
                ],
            ]);
        } catch (GuzzleException $e) {
            Log::error('Error fetching nearby station from Traewelling', ['exception' => $e->getMessage(), 'point' => $point]);

            return null;
        }

        $data = json_decode($response->getBody()->getContents(), true);
        $location = $data['data'] ?? null;
        Log::debug('Station nearby response', ['response' => $data, 'point' => $point, 'location' => $location]);

        return $location;
    }

    public function getTrwlStationFromIdentifier(LocationIdentifier $identifier): ?array
    {
        $client = $this->getClient();
        $response = $client->get('stations', [
            'query' => [
                'identifier' => $identifier->identifier,
                'identifier_provider' => 'transitous',
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['data'][0] ?? null;
    }

    /**
     * @throws GuzzleException
     */
    public function checkin(array $data): array
    {
        $response = $this->getClient()->post('trains/checkin', ['json' => $data]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function createTrip(array $data): array
    {
        $client = $this->getClient();
        $response = $client->post('trains/trip', ['json' => $data]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function deletePost(int $traewellingPostId, string $userId): void
    {
        $this->getAccessToken($userId);
        $client = $this->getClient();
        try {
            $response = $client->delete('status/'.$traewellingPostId);
            Log::debug('Deleted post', ['response' => $response->getBody()->getContents()]);
        } catch (GuzzleException $e) {
            Log::error('Error deleting post in Traewelling', ['exception' => $e->getMessage(), 'traewellingPostId' => $traewellingPostId]);
        }
    }

    public function getPostTags(string $traewellingPostId, string $userId): array
    {
        $this->getAccessToken($userId);
        $client = $this->getClient();
        try {
            $response = $client->get('status/'.$traewellingPostId.'/tags');
            $data = json_decode($response->getBody()->getContents(), true);
            Log::debug('Fetched post tags', ['response' => $data]);

            return $data['data'] ?? [];
        } catch (GuzzleException $e) {
            Log::error('Error fetching post tags from Traewelling', ['exception' => $e->getMessage()]);

            return [];
        }
    }

    public function updateOrCreateTag(bool $tagExists, int $traewellingPostId, string $userId, string $key, string $value, int $visibility = 0): void
    {
        if ($tagExists) {
            $this->updateTag($traewellingPostId, $userId, $key, $value, $visibility);
        } else {
            try {
                $this->createTag($traewellingPostId, $userId, $key, $value, $visibility);
            } catch (ConflictHttpException $e) {
                Log::warning('Tag already exists when trying to create, updating instead', ['traewellingPostId' => $traewellingPostId, 'key' => $key]);
                $this->updateTag($traewellingPostId, $userId, $key, $value, $visibility);
            }
        }
    }

    public function deletePostTag(int $traewellingPostId, string $userId, string $key): void
    {
        $this->getAccessToken($userId);
        $client = $this->getClient();
        try {
            $response = $client->delete('status/'.$traewellingPostId.'/tags/'.$key);
            Log::debug('Deleted post tag', ['response' => $response->getBody()->getContents()]);
        } catch (GuzzleException $e) {
            Log::error('Error deleting post tag in Traewelling', ['exception' => $e->getMessage(), 'traewellingPostId' => $traewellingPostId]);
        }
    }

    /**
     * @throws ConflictHttpException
     */
    public function createTag(int $traewellingPostId, string $userId, string $key, string $value, int $visibility = 0): void
    {
        $this->getAccessToken($userId);
        $client = $this->getClient();
        try {
            $response = $client->post('status/'.$traewellingPostId.'/tags', ['json' => [
                'key' => $key,
                'value' => $value,
                'visibility' => $visibility,
            ]]);
            Log::debug('Created post tag', ['response' => $response->getBody()->getContents()]);
        } catch (GuzzleException $e) {
            if ($e->getCode() === 400) {
                throw new ConflictHttpException;
            }
            Log::error('Error creating post tag in Traewelling', ['exception' => $e->getMessage(), 'traewellingPostId' => $traewellingPostId]);
        }
    }

    public function updateTag(int $traewellingPostId, string $userId, string $key, string $value, int $visibility = 0): void
    {
        $this->getAccessToken($userId);
        $client = $this->getClient();
        try {
            $response = $client->put('status/'.$traewellingPostId.'/tags/'.$key, ['json' => [
                'key' => $key,
                'value' => $value,
                'visibility' => $visibility,
            ]]);
            Log::debug('Created post tag', ['response' => $response->getBody()->getContents()]);
        } catch (GuzzleException $e) {
            if ($e->getCode() === 400) {
                throw new ConflictHttpException;
            }
            Log::error('Error creating post tag in Traewelling', ['exception' => $e->getMessage(), 'traewellingPostId' => $traewellingPostId]);
        }
    }

    public function updatePost(int $traewellingPostId, string $userId, array $data): void
    {
        $this->getAccessToken($userId);
        $client = $this->getClient();
        try {
            $response = $client->put('status/'.$traewellingPostId, ['json' => $data]);
            Log::debug('Updated post', ['response' => $response->getBody()->getContents()]);
        } catch (GuzzleException $e) {
            Log::error('Error updating post in Traewelling', ['exception' => $e->getMessage(), 'traewellingPostId' => $traewellingPostId]);
        }
    }
}
