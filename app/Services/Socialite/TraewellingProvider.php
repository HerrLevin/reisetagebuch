<?php

namespace App\Services\Socialite;

use App\Services\VersionService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;
use LogicException;

class TraewellingProvider extends AbstractProvider implements ProviderInterface
{
    protected $scopes = ['write-statuses', 'read-statuses'];

    protected $scopeSeparator = ' ';

    public static function make(): self
    {
        $provider = Socialite::driver('traewelling');
        if (! $provider instanceof self) {
            throw new LogicException('The traewelling Socialite driver must be a '.self::class);
        }

        return $provider;
    }

    public function user(): TraewellingUser
    {
        $user = parent::user();
        if (! $user instanceof TraewellingUser) {
            throw new LogicException('Traewelling users must be mapped to '.TraewellingUser::class);
        }

        return $user;
    }

    public function __construct(Request $request, $clientId, $clientSecret, $redirectUrl, $guzzle = [], ?VersionService $versionService = null)
    {
        $versionService ??= app(VersionService::class);
        parent::__construct($request, $clientId, $clientSecret, $redirectUrl, array_merge_recursive($guzzle, [
            'headers' => [
                'User-Agent' => $versionService->getUserAgent(),
            ],
        ]));
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
