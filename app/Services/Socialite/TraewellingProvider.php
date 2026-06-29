<?php

namespace App\Services\Socialite;

use App\Services\VersionService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class TraewellingProvider extends AbstractProvider implements ProviderInterface
{
    protected $scopes = ['write-statuses', 'read-statuses'];

    protected $scopeSeparator = ' ';

    private VersionService $versionService;

    public function __construct(Request $request, $clientId, $clientSecret, $redirectUrl, $guzzle = [], ?VersionService $versionService = null)
    {
        parent::__construct($request, $clientId, $clientSecret, $redirectUrl, $guzzle);
        $this->versionService = $versionService ?? app(VersionService::class);
    }

    protected function getHttpClient(): Client
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new Client(array_merge_recursive($this->guzzle, [
                'headers' => [
                    'User-Agent' => $this->versionService->getUserAgent(),
                ],
            ]));
        }

        return $this->httpClient;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(config('services.traewelling.base_uri').'/oauth/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl(): string
    {
        return config('services.traewelling.base_uri').'/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            config('services.traewelling.base_uri').'/api/v1/auth/user',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                ],
            ]
        );

        return json_decode($response->getBody(), true)['data'] ?? [];
    }

    public function logout($token): void
    {
        $this->getHttpClient()->post(
            config('services.traewelling.base_uri').'/api/v1/auth/logout',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user): User|TraewellingUser
    {
        return new TraewellingUser()->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => $user['username'] ?? null,
            'name' => $user['name'] ?? null,
            'email' => $user['email'] ?? null,
            'avatar' => $user['avatar'] ?? null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code): array
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
        ]);
    }
}
