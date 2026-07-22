<?php

namespace Tests\Feature\Controllers\ActivityPub;

use App\Http\Middleware\VerifyHttpSignature;
use App\Models\ActivityPubActor;
use App\Models\ActivityPubFollower;
use App\Models\ActivityPubLike;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Tests für die ActivityPub-Endpunkte (Außengrenzen / Black-Box-Tests).
 *
 * Getestete Routen:
 *   GET  /.well-known/nodeinfo
 *   GET  /nodeinfo/2.0
 *   GET  /.well-known/webfinger?resource=acct:…
 *   GET  /ap/users/{username}          (Actor)
 *   GET  /ap/users/{username}/outbox
 *   GET  /ap/users/{username}/followers
 *   GET  /ap/users/{username}/following
 *   POST /ap/users/{username}/inbox    (requires HTTP-Signature)
 *   POST /ap/inbox                     (shared inbox, requires HTTP-Signature)
 *   GET  /ap/posts/{id}
 *   GET  /ap/posts/{id}/object
 */
class ActivityPubControllerTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Erstellt ein RSA-Schlüsselpaar und gibt [privateKeyPem, publicKeyPem] zurück.
     */
    private function generateKeyPair(): array
    {
        $resource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        openssl_pkey_export($resource, $privateKeyPem);
        $publicKeyPem = openssl_pkey_get_details($resource)['key'];

        return [$privateKeyPem, $publicKeyPem];
    }

    /**
     * Erstellt einen User mit gültigem RSA-Schlüsselpaar in der DB.
     */
    private function createUserWithKeys(array $attributes = []): User
    {
        [$privateKeyPem, $publicKeyPem] = $this->generateKeyPair();

        return User::factory()->create(array_merge([
            'public_key' => $publicKeyPem,
            'private_key' => $privateKeyPem,
        ], $attributes));
    }

    /**
     * Baut einen gültigen HTTP-Signature-Header und liefert alle nötigen Header
     * für einen signierten POST-Request zurück.
     *
     * Datum im RFC 7231-Format: "Mon, 01 Jan 2024 00:00:00 GMT"
     */
    private function buildSignedHeaders(
        string $privateKeyPem,
        string $keyId,
        string $path,
        string $body,
        string $host
    ): array {
        $date = now()->format('D, d M Y H:i:s \G\M\T');
        $digest = 'SHA-256='.base64_encode(hash('sha256', $body, true));

        // content-type wird bewusst nicht mitsigniert: Laravels postJson() erzwingt
        // immer "Content-Type: application/json" auf dem tatsächlichen Request,
        // unabhängig vom hier gesetzten Header — genau wie die echte Federation-
        // Implementierung (ActivityPubService::createSignature()) signiert dieser
        // Helper daher nur (request-target) host date digest.
        $signingString = implode("\n", [
            "(request-target): post {$path}",
            "host: {$host}",
            "date: {$date}",
            "digest: {$digest}",
        ]);

        $privateKey = openssl_pkey_get_private($privateKeyPem);
        openssl_sign($signingString, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $signatureB64 = base64_encode($signature);

        $signatureHeader = implode(',', [
            "keyId=\"{$keyId}\"",
            'algorithm="rsa-sha256"',
            'headers="(request-target) host date digest"',
            "signature=\"{$signatureB64}\"",
        ]);

        return [
            'Date' => $date,
            'Digest' => $digest,
            'Signature' => $signatureHeader,
            'Host' => $host,
        ];
    }

    /**
     * Mockt HTTP-Aufrufe, die ActivityPubService und VerifyHttpSignature machen.
     * Stellt sicher, dass der Public Key im Cache liegt, damit die Middleware
     * keinen echten HTTP-Request machen muss.
     */
    private function setupRemoteActor(
        string $actorId,
        string $publicKeyPem,
        string $inbox = 'https://remote.example/inbox'
    ): void {
        // Cache vorbelegen – Middleware schaut hier zuerst nach dem Public Key
        $keyId = "{$actorId}#main-key";
        $cacheKey = 'ap_public_key:'.md5($keyId);
        Cache::put($cacheKey, $publicKeyPem, 3600);

        // Http::fake() für ActivityPubService::getActorProfile()-Aufrufe
        Http::fake([
            $actorId.'*' => Http::response([
                'id' => $actorId,
                'type' => 'Person',
                'inbox' => $inbox,
                'endpoints' => ['sharedInbox' => 'https://remote.example/shared-inbox'],
                'preferredUsername' => 'remoteuser',
                'name' => 'Remote User',
                'url' => 'https://remote.example/@remoteuser',
                'publicKey' => [
                    'id' => $keyId,
                    'owner' => $actorId,
                    'publicKeyPem' => $publicKeyPem,
                ],
            ], 200, ['Content-Type' => 'application/activity+json']),
            '*' => Http::response([], 404),
        ]);
    }

    /**
     * Führt einen signierten POST-Request auf einen Inbox-Endpunkt durch.
     */
    private function signedPost(
        string $url,
        array $activity,
        string $actorId,
        string $privateKeyPem
    ) {
        $host = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';
        $path = parse_url($url, PHP_URL_PATH);
        // Muss exakt dem entsprechen, was postJson() tatsächlich sendet
        // (json_encode() ohne JSON_UNESCAPED_SLASHES), sonst stimmt der Digest nicht.
        $body = json_encode($activity);
        $keyId = "{$actorId}#main-key";
        $headers = $this->buildSignedHeaders($privateKeyPem, $keyId, $path, $body, $host);

        return $this->withHeaders($headers)->postJson($url, $activity);
    }

    /**
     * Wie signedPost(), erlaubt aber explizite Kontrolle darüber, welche Header
     * signiert werden und ob der Digest-Header überhaupt gesendet wird — für
     * Tests der Digest-Pflicht (Fix 2).
     *
     * @param  string[]  $signedHeaderNames
     */
    private function signedPostCustomHeaders(
        string $url,
        array $activity,
        string $actorId,
        string $privateKeyPem,
        array $signedHeaderNames,
        bool $includeDigestHeader,
    ): TestResponse {
        $host = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';
        $path = parse_url($url, PHP_URL_PATH);
        $body = json_encode($activity);
        $date = now()->format('D, d M Y H:i:s \G\M\T');
        $digest = 'SHA-256='.base64_encode(hash('sha256', $body, true));

        $availableParts = [
            '(request-target)' => "(request-target): post {$path}",
            'host' => "host: {$host}",
            'date' => "date: {$date}",
            'digest' => "digest: {$digest}",
            'content-type' => 'content-type: application/activity+json',
        ];
        $signingString = implode("\n", array_map(fn ($name) => $availableParts[$name], $signedHeaderNames));

        $privateKey = openssl_pkey_get_private($privateKeyPem);
        openssl_sign($signingString, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $signatureB64 = base64_encode($signature);

        $signatureHeader = implode(',', [
            'keyId="'.$actorId.'#main-key"',
            'algorithm="rsa-sha256"',
            'headers="'.implode(' ', $signedHeaderNames).'"',
            "signature=\"{$signatureB64}\"",
        ]);

        $headers = [
            'Date' => $date,
            'Signature' => $signatureHeader,
            'Content-Type' => 'application/activity+json',
            'Host' => $host,
        ];
        if ($includeDigestHeader) {
            $headers['Digest'] = $digest;
        }

        return $this->withHeaders($headers)->postJson($url, $activity);
    }

    /**
     * Ersetzt die VerifyHttpSignature-Middleware durch eine Fake-Variante, die
     * den signierenden Actor als bereits verifiziert markiert (ohne echte
     * Signaturprüfung), damit reine Controller-Logik-Tests unabhängig von der
     * HTTP-Signature-Verifikation bleiben (die separat getestet wird).
     */
    private function withVerifiedActor(?string $actorId): static
    {
        $this->app->bind(VerifyHttpSignature::class, fn () => new class($actorId)
        {
            public function __construct(private readonly ?string $actorId) {}

            public function handle($request, \Closure $next)
            {
                if ($this->actorId !== null) {
                    $request->attributes->set('ap_verified_actor', $this->actorId);
                }

                return $next($request);
            }
        });

        return $this;
    }

    /**
     * POST auf einen Inbox-Endpunkt, bei dem die HTTP-Signature-Verifikation
     * durch eine Fake-Middleware ersetzt wird (für reine Controller-Logik-Tests).
     */
    private function inboxPost(string $url, array $activity): TestResponse
    {
        return $this
            ->withVerifiedActor($activity['actor'] ?? null)
            ->withHeaders(['Content-Type' => 'application/activity+json'])
            ->postJson($url, $activity);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // /.well-known/nodeinfo
    // ──────────────────────────────────────────────────────────────────────────

    public function test_well_known_nodeinfo_returns_link_document(): void
    {
        $response = $this->getJson('/.well-known/nodeinfo');

        $response->assertOk()
            ->assertJsonStructure(['links' => [['rel', 'href']]])
            ->assertJsonFragment(['rel' => 'http://nodeinfo.diaspora.software/ns/schema/2.0']);
    }

    public function test_well_known_nodeinfo_href_points_to_nodeinfo_20(): void
    {
        $response = $this->getJson('/.well-known/nodeinfo');

        $href = $response->json('links.0.href');
        $this->assertStringContainsString('/nodeinfo/2.0', $href);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // /nodeinfo/2.0
    // ──────────────────────────────────────────────────────────────────────────

    public function test_nodeinfo_20_returns_valid_structure(): void
    {
        $response = $this->getJson('/nodeinfo/2.0');

        $response->assertOk()
            ->assertJsonStructure([
                'version',
                'software' => ['name', 'version'],
                'protocols',
                'usage' => ['users' => ['total', 'activeMonth', 'activeHalfyear'], 'localPosts'],
            ]);
    }

    public function test_nodeinfo_20_advertises_activitypub(): void
    {
        $response = $this->getJson('/nodeinfo/2.0');

        $this->assertContains('activitypub', $response->json('protocols'));
    }

    public function test_nodeinfo_20_user_counts_reflect_database(): void
    {
        User::factory()->count(3)->create();

        $response = $this->getJson('/nodeinfo/2.0');

        $this->assertGreaterThanOrEqual(3, $response->json('usage.users.total'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // /.well-known/webfinger
    // ──────────────────────────────────────────────────────────────────────────

    public function test_webfinger_resolves_known_user(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);
        $host = parse_url(config('app.url'), PHP_URL_HOST);

        $response = $this->get("/.well-known/webfinger?resource=acct:alice@{$host}");

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/jrd+json')
            ->assertJsonFragment(['subject' => "acct:alice@{$host}"])
            ->assertJsonStructure(['subject', 'links']);
    }

    public function test_webfinger_response_contains_self_link(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);
        $host = parse_url(config('app.url'), PHP_URL_HOST);

        $response = $this->getJson("/.well-known/webfinger?resource=acct:alice@{$host}");

        $links = collect($response->json('links'));
        $selfLink = $links->firstWhere('rel', 'self');

        $this->assertNotNull($selfLink, 'Self-Link fehlt im WebFinger-Response');
        $this->assertSame('application/activity+json', $selfLink['type']);
        $this->assertStringContainsString('/ap/users/alice', $selfLink['href']);
    }

    public function test_webfinger_returns_404_for_unknown_user(): void
    {
        $host = parse_url(config('app.url'), PHP_URL_HOST);

        $response = $this->getJson("/.well-known/webfinger?resource=acct:nobody@{$host}");

        $response->assertNotFound();
    }

    public function test_webfinger_returns_400_without_resource_parameter(): void
    {
        $response = $this->getJson('/.well-known/webfinger');

        $response->assertStatus(400);
    }

    public function test_webfinger_returns_400_for_invalid_resource_format(): void
    {
        $response = $this->getJson('/.well-known/webfinger?resource=invalid');

        $response->assertStatus(400);
    }

    public function test_webfinger_returns_400_for_wrong_domain(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);

        $response = $this->getJson('/.well-known/webfinger?resource=acct:alice@other.example');

        $response->assertStatus(400);
    }

    public function test_webfinger_accepts_leading_at_in_resource(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);
        $host = parse_url(config('app.url'), PHP_URL_HOST);

        // Format: acct:@alice@host (mit führendem @)
        $response = $this->getJson("/.well-known/webfinger?resource=acct:@alice@{$host}");

        $response->assertOk()
            ->assertJsonFragment(['subject' => "acct:@alice@{$host}"]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /ap/users/{username}  (Actor)
    // ──────────────────────────────────────────────────────────────────────────

    public function test_actor_endpoint_returns_person_object_with_ap_header(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);

        $response = $this->withHeaders(['Accept' => 'application/activity+json'])
            ->get('/ap/users/alice');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/activity+json')
            ->assertJsonFragment(['type' => 'Person'])
            ->assertJsonFragment(['preferredUsername' => 'alice']);
    }

    public function test_actor_endpoint_contains_required_ap_fields(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);

        $response = $this->withHeaders(['Accept' => 'application/activity+json'])
            ->getJson('/ap/users/alice');

        $response->assertJsonStructure([
            '@context',
            'id',
            'type',
            'inbox',
            'outbox',
            'followers',
            'following',
            'preferredUsername',
            'publicKey' => ['id', 'owner', 'publicKeyPem'],
        ]);
    }

    public function test_actor_endpoint_redirects_for_html_accept_header(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);

        $response = $this->withHeaders(['Accept' => 'text/html'])
            ->get('/ap/users/alice');

        $response->assertRedirect();
    }

    public function test_actor_endpoint_returns_404_for_unknown_user(): void
    {
        $response = $this->withHeaders(['Accept' => 'application/activity+json'])
            ->get('/ap/users/nobody');

        $response->assertNotFound();
    }

    public function test_actor_endpoint_includes_icon_when_user_has_avatar(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $user->profile()->create(['avatar' => 'avatars/alice.jpg', 'avatar_mime_type' => 'image/jpeg']);

        $response = $this->withHeaders(['Accept' => 'application/activity+json'])
            ->getJson('/ap/users/alice');

        $response->assertJsonStructure(['icon' => ['type', 'mediaType', 'url']]);
    }

    public function test_actor_endpoint_omits_icon_when_user_has_no_avatar(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);

        $response = $this->withHeaders(['Accept' => 'application/activity+json'])
            ->getJson('/ap/users/alice');

        $this->assertNull($response->json('icon'));
    }

    public function test_actor_endpoint_includes_image_when_user_has_header(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $user->profile()->create(['header' => 'headers/alice.jpg', 'header_mime_type' => 'image/jpeg']);

        $response = $this->withHeaders(['Accept' => 'application/activity+json'])
            ->getJson('/ap/users/alice');

        $response->assertJsonStructure(['image' => ['type', 'mediaType', 'url']]);
    }

    public function test_actor_endpoint_omits_image_when_user_has_no_header(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);

        $response = $this->withHeaders(['Accept' => 'application/activity+json'])
            ->getJson('/ap/users/alice');

        $this->assertNull($response->json('icon'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /ap/users/{username}/outbox
    // ──────────────────────────────────────────────────────────────────────────

    public function test_outbox_without_page_returns_ordered_collection(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);

        $response = $this->getJson('/ap/users/alice/outbox');

        $response->assertOk()
            ->assertJsonFragment(['type' => 'OrderedCollection'])
            ->assertJsonStructure(['@context', 'id', 'type', 'totalItems', 'first']);
    }

    public function test_outbox_with_page_true_returns_ordered_collection_page(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);

        $response = $this->getJson('/ap/users/alice/outbox?page=true');

        $response->assertOk()
            ->assertJsonFragment(['type' => 'OrderedCollectionPage'])
            ->assertJsonStructure(['@context', 'id', 'type', 'partOf', 'orderedItems']);
    }

    public function test_outbox_page_items_have_create_activity_structure(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        Post::factory()->count(2)->create(['user_id' => $user->id]);

        $response = $this->getJson('/ap/users/alice/outbox?page=true');

        $items = $response->json('orderedItems');
        $this->assertNotEmpty($items);

        foreach ($items as $item) {
            $this->assertSame('Create', $item['type']);
            $this->assertArrayHasKey('actor', $item);
            $this->assertArrayHasKey('object', $item);
            $this->assertSame('Note', $item['object']['type']);
        }
    }

    public function test_outbox_returns_404_for_unknown_user(): void
    {
        $response = $this->getJson('/ap/users/nobody/outbox');

        $response->assertNotFound();
    }

    public function test_outbox_returns_ap_content_type_header(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);

        $response = $this->getJson('/ap/users/alice/outbox');

        $response->assertHeader('Content-Type', 'application/activity+json');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /ap/users/{username}/followers
    // ──────────────────────────────────────────────────────────────────────────

    public function test_followers_returns_ordered_collection(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);

        $response = $this->getJson('/ap/users/alice/followers');

        $response->assertOk()
            ->assertJsonFragment(['type' => 'OrderedCollection'])
            ->assertJsonStructure(['@context', 'id', 'type', 'totalItems']);
    }

    public function test_followers_count_reflects_database(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);

        foreach (['bob', 'carol', 'dave'] as $name) {
            ActivityPubFollower::factory()->create([
                'follower_actor_id' => "https://remote.example/users/{$name}",
                'followed_user_id' => $user->id,
            ]);
        }

        $response = $this->getJson('/ap/users/alice/followers');

        $this->assertSame(3, $response->json('totalItems'));
    }

    public function test_followers_returns_404_for_unknown_user(): void
    {
        $response = $this->getJson('/ap/users/nobody/followers');

        $response->assertNotFound();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /ap/users/{username}/following
    // ──────────────────────────────────────────────────────────────────────────

    public function test_following_returns_ordered_collection_with_zero_items(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);

        $response = $this->getJson('/ap/users/alice/following');

        $response->assertOk()
            ->assertJsonFragment(['type' => 'OrderedCollection'])
            ->assertJsonFragment(['totalItems' => 0]);
    }

    public function test_following_returns_404_for_unknown_user(): void
    {
        $response = $this->getJson('/ap/users/nobody/following');

        $response->assertNotFound();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /ap/posts/{id}  und  /ap/posts/{id}/object
    // ──────────────────────────────────────────────────────────────────────────

    public function test_post_object_returns_note_with_ap_content_type(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create(['user_id' => $user->id, 'body' => 'Hallo Fediverse!']);

        $response = $this->withHeaders(['Accept' => 'application/activity+json'])
            ->get("/ap/posts/{$post->id}/object");

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/activity+json')
            ->assertJsonFragment(['type' => 'Note']);
    }

    public function test_post_object_redirects_for_browser_accept_header(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders(['Accept' => 'text/html'])
            ->get("/ap/posts/{$post->id}/object");

        $response->assertRedirect();
    }

    public function test_post_object_via_activity_route_returns_same_note(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create(['user_id' => $user->id]);

        $r1 = $this->withHeaders(['Accept' => 'application/activity+json'])
            ->getJson("/ap/posts/{$post->id}");
        $r2 = $this->withHeaders(['Accept' => 'application/activity+json'])
            ->getJson("/ap/posts/{$post->id}/object");

        $this->assertSame($r1->json('type'), $r2->json('type'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /ap/users/{username}/inbox  –  HTTP-Signature-Pflicht (Middleware)
    // ──────────────────────────────────────────────────────────────────────────

    public function test_inbox_rejects_request_without_signature_header(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);

        $response = $this->postJson('/ap/users/alice/inbox', ['type' => 'Follow'], [
            'Content-Type' => 'application/activity+json',
        ]);

        $response->assertStatus(401);
    }

    public function test_inbox_rejects_invalid_signature(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);
        [, $remotePubKey] = $this->generateKeyPair();
        [$wrongPrivKey] = $this->generateKeyPair(); // anderes Schlüsselpaar
        $remoteActorId = 'https://remote.example/users/bob';

        $this->setupRemoteActor($remoteActorId, $remotePubKey);

        $activity = [
            'type' => 'Follow',
            'actor' => $remoteActorId,
            'object' => route('ap.actor', ['username' => 'alice']),
        ];

        // Signiert mit dem FALSCHEN privaten Schlüssel → Verifizierung schlägt fehl
        $response = $this->signedPost('/ap/users/alice/inbox', $activity, $remoteActorId, $wrongPrivKey);

        $response->assertStatus(401);
    }

    public function test_inbox_rejects_tampered_digest(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);
        [$remotePrivKey, $remotePubKey] = $this->generateKeyPair();
        $remoteActorId = 'https://remote.example/users/bob';

        $this->setupRemoteActor($remoteActorId, $remotePubKey);

        $activity = [
            'type' => 'Follow',
            'actor' => $remoteActorId,
            'object' => route('ap.actor', ['username' => 'alice']),
        ];

        $host = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';
        $body = json_encode($activity, JSON_UNESCAPED_SLASHES);
        $headers = $this->buildSignedHeaders($remotePrivKey, "{$remoteActorId}#main-key", '/ap/users/alice/inbox', $body, $host);

        // Digest nachträglich verfälschen
        $headers['Digest'] = 'SHA-256='.base64_encode(hash('sha256', 'manipulated', true));

        $response = $this->withHeaders($headers)->postJson('/ap/users/alice/inbox', $activity);

        $response->assertStatus(401);
    }

    public function test_inbox_rejects_body_when_digest_header_missing(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);
        [$remotePrivKey, $remotePubKey] = $this->generateKeyPair();
        $remoteActorId = 'https://remote.example/users/bob';

        $this->setupRemoteActor($remoteActorId, $remotePubKey);

        $activity = [
            'type' => 'Follow',
            'actor' => $remoteActorId,
            'object' => route('ap.actor', ['username' => 'alice']),
        ];

        $response = $this->signedPostCustomHeaders(
            '/ap/users/alice/inbox',
            $activity,
            $remoteActorId,
            $remotePrivKey,
            signedHeaderNames: ['(request-target)', 'host', 'date'],
            includeDigestHeader: false,
        );

        $response->assertStatus(401);
    }

    public function test_inbox_rejects_body_when_digest_not_included_in_signed_headers(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);
        [$remotePrivKey, $remotePubKey] = $this->generateKeyPair();
        $remoteActorId = 'https://remote.example/users/bob';

        $this->setupRemoteActor($remoteActorId, $remotePubKey);

        $activity = [
            'type' => 'Follow',
            'actor' => $remoteActorId,
            'object' => route('ap.actor', ['username' => 'alice']),
        ];

        // Digest-Header wird gesendet, ist aber nicht Teil der signierten Header
        // → die Signatur deckt den Body nicht ab.
        $response = $this->signedPostCustomHeaders(
            '/ap/users/alice/inbox',
            $activity,
            $remoteActorId,
            $remotePrivKey,
            signedHeaderNames: ['(request-target)', 'host', 'date'],
            includeDigestHeader: true,
        );

        $response->assertStatus(401);
    }

    public function test_inbox_rejects_activity_actor_that_does_not_match_signer(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);
        [$bobPrivKey, $bobPubKey] = $this->generateKeyPair();
        $bobActorId = 'https://remote.example/users/bob';

        $this->setupRemoteActor($bobActorId, $bobPubKey);

        $activity = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => 'https://remote.example/activities/forged-1',
            'type' => 'Follow',
            'actor' => 'https://remote.example/users/carol', // fälschlich als Carol ausgegeben
            'object' => route('ap.actor', ['username' => 'alice']),
        ];

        // Signiert mit Bobs Schlüssel, behauptet aber Carol zu sein.
        $response = $this->signedPost('/ap/users/alice/inbox', $activity, $bobActorId, $bobPrivKey);

        $response->assertStatus(403);
    }

    public function test_inbox_rejects_undo_follow_when_inner_actor_does_not_match_outer(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);
        $remoteActorId = 'https://remote.example/users/bob';

        $activity = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => 'https://remote.example/activities/forged-undo',
            'type' => 'Undo',
            'actor' => $remoteActorId,
            'object' => [
                'type' => 'Follow',
                'actor' => 'https://remote.example/users/carol', // gefälschter innerer Actor
                'object' => route('ap.actor', ['username' => 'alice']),
            ],
        ];

        $response = $this->inboxPost('/ap/users/alice/inbox', $activity);

        $response->assertStatus(403);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /ap/users/{username}/inbox  –  Controller-Logik (Middleware bypassed)
    //
    // Diese Tests prüfen das Controller-Verhalten unabhängig von der
    // HTTP-Signature-Verifikation (die separat getestet wird).
    // ──────────────────────────────────────────────────────────────────────────

    public function test_inbox_rejects_wrong_content_type(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);

        $response = $this
            ->withoutMiddleware(VerifyHttpSignature::class)
            ->post('/ap/users/alice/inbox', [], [
                'Content-Type' => 'text/plain',
            ]);

        $response->assertStatus(415);
    }

    public function test_inbox_handles_follow_activity_and_stores_follower(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        [, $remotePubKey] = $this->generateKeyPair();
        $remoteActorId = 'https://remote.example/users/bob';

        $this->setupRemoteActor($remoteActorId, $remotePubKey);

        $activity = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => 'https://remote.example/activities/1',
            'type' => 'Follow',
            'actor' => $remoteActorId,
            'object' => route('ap.actor', ['username' => 'alice']),
        ];

        $response = $this->inboxPost('/ap/users/alice/inbox', $activity);

        $response->assertStatus(202);
        $this->assertDatabaseHas('activity_pub_followers', [
            'follower_actor_id' => $remoteActorId,
            'followed_user_id' => $user->id,
        ]);
    }

    public function test_inbox_follow_is_idempotent_and_does_not_create_duplicate(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        [, $remotePubKey] = $this->generateKeyPair();
        $remoteActorId = 'https://remote.example/users/bob';

        $this->setupRemoteActor($remoteActorId, $remotePubKey);

        $activity = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => 'https://remote.example/activities/1',
            'type' => 'Follow',
            'actor' => $remoteActorId,
            'object' => route('ap.actor', ['username' => 'alice']),
        ];

        $this->inboxPost('/ap/users/alice/inbox', $activity);
        $this->inboxPost('/ap/users/alice/inbox', $activity);

        $this->assertDatabaseCount('activity_pub_followers', 1);
    }

    public function test_inbox_follow_returns_400_for_wrong_object(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);
        [, $remotePubKey] = $this->generateKeyPair();
        $remoteActorId = 'https://remote.example/users/bob';

        $this->setupRemoteActor($remoteActorId, $remotePubKey);

        $activity = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => 'https://remote.example/activities/2',
            'type' => 'Follow',
            'actor' => $remoteActorId,
            'object' => 'https://other.example/users/charlie', // falsche Person
        ];

        $response = $this->inboxPost('/ap/users/alice/inbox', $activity);

        $response->assertStatus(400);
    }

    public function test_inbox_undo_follow_removes_follower(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        [, $remotePubKey] = $this->generateKeyPair();
        $remoteActorId = 'https://remote.example/users/bob';

        $actor = ActivityPubActor::create([
            'actor_uri' => $remoteActorId,
            'inbox_url' => 'https://remote.example/inbox',
            'shared_inbox_url' => null,
        ]);
        ActivityPubFollower::factory()->create([
            'follower_actor_id' => $remoteActorId,
            'followed_user_id' => $user->id,
            'activity_pub_actor_id' => $actor->id,
        ]);

        $this->setupRemoteActor($remoteActorId, $remotePubKey);

        $activity = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => 'https://remote.example/activities/3',
            'type' => 'Undo',
            'actor' => $remoteActorId,
            'object' => [
                'type' => 'Follow',
                'actor' => $remoteActorId,
                'object' => route('ap.actor', ['username' => 'alice']),
            ],
        ];

        $response = $this->inboxPost('/ap/users/alice/inbox', $activity);

        $response->assertStatus(202);
        $this->assertDatabaseMissing('activity_pub_followers', [
            'follower_actor_id' => $remoteActorId,
            'followed_user_id' => $user->id,
        ]);
    }

    public function test_inbox_undo_follow_is_idempotent_when_follower_does_not_exist(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);
        $remoteActorId = 'https://remote.example/users/bob';

        $activity = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => 'https://remote.example/activities/4',
            'type' => 'Undo',
            'actor' => $remoteActorId,
            'object' => [
                'type' => 'Follow',
                'actor' => $remoteActorId,
                'object' => route('ap.actor', ['username' => 'alice']),
            ],
        ];

        $response = $this->inboxPost('/ap/users/alice/inbox', $activity);

        $response->assertStatus(202);
    }

    public function test_inbox_like_activity_stores_like(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create(['user_id' => $user->id]);
        [, $remotePubKey] = $this->generateKeyPair();
        $remoteActorId = 'https://remote.example/users/bob';

        $this->setupRemoteActor($remoteActorId, $remotePubKey);

        $activity = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => 'https://remote.example/activities/like-1',
            'type' => 'Like',
            'actor' => $remoteActorId,
            'object' => route('ap.post', ['id' => $post->id]),
        ];

        $response = $this->inboxPost('/ap/users/alice/inbox', $activity);

        $response->assertStatus(202);
        $this->assertDatabaseHas('activity_pub_likes', [
            'actor_id' => $remoteActorId,
            'post_id' => $post->id,
        ]);
    }

    public function test_inbox_like_activity_is_idempotent(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create(['user_id' => $user->id]);
        [, $remotePubKey] = $this->generateKeyPair();
        $remoteActorId = 'https://remote.example/users/bob';

        $this->setupRemoteActor($remoteActorId, $remotePubKey);

        $activity = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => 'https://remote.example/activities/like-1',
            'type' => 'Like',
            'actor' => $remoteActorId,
            'object' => route('ap.post', ['id' => $post->id]),
        ];

        $this->inboxPost('/ap/users/alice/inbox', $activity);
        $this->inboxPost('/ap/users/alice/inbox', $activity);

        $this->assertDatabaseCount('activity_pub_likes', 1);
    }

    public function test_inbox_undo_like_removes_like(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create(['user_id' => $user->id]);
        $remoteActorId = 'https://remote.example/users/bob';

        ActivityPubLike::create([
            'actor_id' => $remoteActorId,
            'post_id' => $post->id,
            'activity_id' => 'https://remote.example/activities/like-1',
        ]);

        $activity = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => 'https://remote.example/activities/undo-like-1',
            'type' => 'Undo',
            'actor' => $remoteActorId,
            'object' => [
                'type' => 'Like',
                'actor' => $remoteActorId,
                'object' => route('ap.post', ['id' => $post->id]),
            ],
        ];

        $response = $this->inboxPost('/ap/users/alice/inbox', $activity);

        $response->assertStatus(202);
        $this->assertDatabaseMissing('activity_pub_likes', [
            'actor_id' => $remoteActorId,
            'post_id' => $post->id,
        ]);
    }

    public function test_inbox_unknown_activity_type_returns_202(): void
    {
        $this->createUserWithKeys(['username' => 'alice']);

        $activity = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => 'https://remote.example/activities/5',
            'type' => 'Announce',
            'actor' => 'https://remote.example/users/bob',
            'object' => 'https://remote.example/posts/1',
        ];

        $response = $this->inboxPost('/ap/users/alice/inbox', $activity);

        $response->assertStatus(202);
    }

    public function test_inbox_like_for_post_belonging_to_different_user_is_silently_ignored(): void
    {
        $alice = $this->createUserWithKeys(['username' => 'alice']);
        $bob = $this->createUserWithKeys(['username' => 'bob']);
        $post = Post::factory()->create(['user_id' => $bob->id]); // Bobs Post

        $activity = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => 'https://remote.example/activities/like-x',
            'type' => 'Like',
            'actor' => 'https://remote.example/users/carol',
            'object' => route('ap.post', ['id' => $post->id]),
        ];

        // Request geht an Alices Inbox, aber Post gehört Bob → ignorieren
        $response = $this->inboxPost('/ap/users/alice/inbox', $activity);

        $response->assertStatus(202);
        $this->assertDatabaseCount('activity_pub_likes', 0);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /ap/inbox  (Shared Inbox)
    // ──────────────────────────────────────────────────────────────────────────

    public function test_shared_inbox_rejects_request_without_signature(): void
    {
        $response = $this->postJson('/ap/inbox', ['type' => 'Follow'], [
            'Content-Type' => 'application/activity+json',
        ]);

        $response->assertStatus(401);
    }

    public function test_shared_inbox_accepts_follow_for_known_user(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        [, $remotePubKey] = $this->generateKeyPair();
        $remoteActorId = 'https://remote.example/users/bob';

        $this->setupRemoteActor($remoteActorId, $remotePubKey);

        $aliceActorUrl = route('ap.actor', ['username' => 'alice']);
        $activity = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => 'https://remote.example/activities/si-1',
            'type' => 'Follow',
            'actor' => $remoteActorId,
            'object' => $aliceActorUrl,
        ];

        $response = $this
            ->withVerifiedActor($activity['actor'] ?? null)
            ->withHeaders(['Content-Type' => 'application/activity+json'])
            ->postJson('/ap/inbox', $activity);

        $response->assertStatus(202);
        $this->assertDatabaseHas('activity_pub_followers', [
            'follower_actor_id' => $remoteActorId,
            'followed_user_id' => $user->id,
        ]);
    }

    public function test_shared_inbox_returns_202_when_target_actor_not_parseable(): void
    {
        // object ist kein String → targetActorId kann nicht ermittelt werden
        $activity = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => 'https://remote.example/activities/si-2',
            'type' => 'Create',
            'actor' => 'https://remote.example/users/bob',
            'object' => ['type' => 'Note', 'content' => 'Hallo'],
        ];

        $response = $this
            ->withVerifiedActor($activity['actor'] ?? null)
            ->withHeaders(['Content-Type' => 'application/activity+json'])
            ->postJson('/ap/inbox', $activity);

        $response->assertStatus(202);
    }

    public function test_shared_inbox_returns_202_for_unknown_target_user(): void
    {
        // object zeigt auf einen User, der nicht in der DB existiert
        $activity = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => 'https://remote.example/activities/si-3',
            'type' => 'Follow',
            'actor' => 'https://remote.example/users/bob',
            'object' => route('ap.actor', ['username' => 'ghost']),
        ];

        $response = $this
            ->withVerifiedActor($activity['actor'] ?? null)
            ->withHeaders(['Content-Type' => 'application/activity+json'])
            ->postJson('/ap/inbox', $activity);

        $response->assertStatus(202);
    }
}
