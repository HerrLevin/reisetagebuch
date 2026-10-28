<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ClientRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private const string REDIRECT_URI = 'ch.reisetagebu://oauth/callback';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_metadata_advertises_endpoints(): void
    {
        $this->getJson('/.well-known/oauth-authorization-server')
            ->assertOk()
            ->assertJsonPath('registration_endpoint', route('oauth.register'))
            ->assertJsonPath('token_endpoint', route('passport.token'))
            ->assertJsonPath('code_challenge_methods_supported', ['S256']);
    }

    public function test_app_can_register_as_public_client(): void
    {
        $clientId = $this->register()
            ->assertCreated()
            ->assertJsonPath('token_endpoint_auth_method', 'none')
            ->assertJsonPath('redirect_uris', [self::REDIRECT_URI])
            ->assertJsonPath('policy_uri', 'https://app.example/privacy')
            ->assertJsonPath('contacts', ['dev@app.example'])
            ->json('client_id');

        $client = Client::findOrFail($clientId);
        $this->assertSame('https://app.example/terms', $client->tos_uri);
        $this->assertSame(['dev@app.example'], json_decode($client->contacts));
        $this->assertFalse($client->confidential());
        $this->assertSame(['authorization_code', 'refresh_token'], $client->grant_types);
    }

    public static function invalidRegistrations(): array
    {
        return [
            'plain http' => [['redirect_uris' => ['http://evil.example/cb']], 'invalid_redirect_uri'],
            'scheme without dot' => [['redirect_uris' => ['myapp://cb']], 'invalid_redirect_uri'],
            'javascript' => [['redirect_uris' => ['javascript:alert(1)']], 'invalid_redirect_uri'],
            'fragment' => [['redirect_uris' => ['https://app.example/cb#x']], 'invalid_redirect_uri'],
            'client credentials' => [['grant_types' => ['client_credentials']], 'invalid_client_metadata'],
            'confidential' => [['token_endpoint_auth_method' => 'client_secret_basic'], 'invalid_client_metadata'],
            'no name' => [['client_name' => ''], 'invalid_client_metadata'],
            'no client uri' => [['client_uri' => null], 'invalid_client_metadata'],
            'no policy uri' => [['policy_uri' => null], 'invalid_client_metadata'],
            'no tos uri' => [['tos_uri' => null], 'invalid_client_metadata'],
            'http client uri' => [['client_uri' => 'http://app.example'], 'invalid_client_metadata'],
            'invalid contact' => [['contacts' => ['not-an-email']], 'invalid_client_metadata'],
        ];
    }

    #[DataProvider('invalidRegistrations')]
    public function test_invalid_registrations_are_rejected(array $override, string $error): void
    {
        $this->register($override)->assertStatus(400)->assertJsonPath('error', $error);
        $this->assertSame(0, Client::count() - $this->personalAccessClients());
    }

    public function test_contacts_are_optional(): void
    {
        $this->register(['contacts' => null])->assertCreated();
    }

    public function test_loopback_redirect_is_allowed(): void
    {
        $this->register(['redirect_uris' => ['http://127.0.0.1:8123/cb']])->assertCreated();
    }

    public function test_guests_are_sent_to_login_by_authorize(): void
    {
        $this->get($this->authorizeUrl($this->register()->json('client_id'), 'x'))
            ->assertRedirect('/login');
    }

    public function test_app_flow_with_consent_and_pkce(): void
    {
        $this->actingAs(User::factory()->create(), 'web');
        $clientId = $this->register()->json('client_id');
        $verifier = Str::random(64);

        // First authorization shows the consent screen.
        $consent = $this->get($this->authorizeUrl($clientId, $verifier))
            ->assertOk()
            ->assertSee('Reisetagebuch Companion App')
            ->assertSee('https://app.example/privacy')
            ->assertSee(route('passport.authorizations.approve'), false);
        preg_match('/name="auth_token" value="([^"]+)"/', $consent->getContent(), $m);

        $location = $this->post(route('passport.authorizations.approve'), [
            'state' => 'xyz',
            'client_id' => $clientId,
            'auth_token' => $m[1],
        ])->assertRedirect()->headers->get('Location');
        $this->assertStringStartsWith(self::REDIRECT_URI.'?code=', $location);
        parse_str(parse_url($location, PHP_URL_QUERY), $query);

        $this->post('/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'code' => $query['code'],
            'code_verifier' => $verifier,
        ])->assertOk()->assertJsonStructure(['access_token', 'expires_in', 'refresh_token']);

        // Later authorizations skip consent while a token is active.
        $this->assertStringStartsWith(
            self::REDIRECT_URI.'?code=',
            $this->get($this->authorizeUrl($clientId, Str::random(64)))->assertRedirect()->headers->get('Location')
        );
    }

    public function test_unregistered_redirect_uri_is_rejected(): void
    {
        $this->actingAs(User::factory()->create(), 'web');
        $clientId = $this->register()->json('client_id');

        $this->get(str_replace(urlencode(self::REDIRECT_URI), urlencode('https://evil.example/cb'), $this->authorizeUrl($clientId, 'x')))
            ->assertStatus(401);
    }

    private function register(array $override = [])
    {
        return $this->postJson('/oauth/register', [
            'client_name' => 'Reisetagebuch Companion App',
            'redirect_uris' => [self::REDIRECT_URI],
            'token_endpoint_auth_method' => 'none',
            'client_uri' => 'https://app.example',
            'policy_uri' => 'https://app.example/privacy',
            'tos_uri' => 'https://app.example/terms',
            'logo_uri' => 'https://app.example/logo.png',
            'contacts' => ['dev@app.example'],
            ...$override,
        ]);
    }

    private function authorizeUrl(string $clientId, string $verifier): string
    {
        return route('passport.authorizations.authorize', [
            'client_id' => $clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'response_type' => 'code',
            'state' => 'xyz',
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='),
            'code_challenge_method' => 'S256',
        ]);
    }

    private function personalAccessClients(): int
    {
        return Client::where('grant_types', 'like', '%personal_access%')->count();
    }
}
