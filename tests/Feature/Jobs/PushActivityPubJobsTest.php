<?php

namespace Tests\Unit\Jobs;

use App\Enums\Visibility;
use App\Jobs\PushDeleteToMastodon;
use App\Jobs\PushPostToMastodon;
use App\Jobs\PushUpdateToMastodon;
use App\Models\ActivityPubFollower;
use App\Models\Post;
use App\Models\User;
use App\Services\ActivityPubService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PushActivityPubJobsTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function createUserWithKeys(array $attributes = []): User
    {
        $resource = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        openssl_pkey_export($resource, $privateKeyPem);
        $publicKeyPem = openssl_pkey_get_details($resource)['key'];

        return User::factory()->create(array_merge([
            'public_key' => $publicKeyPem,
            'private_key' => $privateKeyPem,
        ], $attributes));
    }

    private function createFollower(User $user, string $actorId, string $inboxUrl, ?string $sharedInboxUrl = null): ActivityPubFollower
    {
        return ActivityPubFollower::create([
            'follower_actor_id' => $actorId,
            'followed_user_id' => $user->id,
            'follower_inbox_url' => $inboxUrl,
            'follower_shared_inbox_url' => $sharedInboxUrl,
        ]);
    }

    /** Stellt sicher, dass Http::fake() einen Deliver-Request aufzeichnet. */
    private function fakeSuccessfulInbox(): void
    {
        Http::fake(['*' => Http::response('', 202)]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PushPostToMastodon – Retry-Konfiguration
    // ──────────────────────────────────────────────────────────────────────────

    public function test_push_post_job_has_correct_retry_configuration(): void
    {
        $job = new PushPostToMastodon('some-id');

        $this->assertSame(3, $job->tries);
        $this->assertSame([30, 120, 600], $job->backoff);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PushPostToMastodon – Bedingungen zum Abbruch ohne Zustellung
    // ──────────────────────────────────────────────────────────────────────────

    public function test_push_post_does_nothing_when_post_not_found(): void
    {
        Http::fake();

        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->never())->method('deliverActivity');

        (new PushPostToMastodon('00000000-0000-7000-8000-000000000000'))->handle($service);

        Http::assertNothingSent();
    }

    public function test_push_post_skips_private_post(): void
    {
        $user = $this->createUserWithKeys();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PRIVATE,
        ]);

        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->never())->method('deliverActivity');

        (new PushPostToMastodon($post->id))->handle($service);
    }

    public function test_push_post_skips_unlisted_post(): void
    {
        $user = $this->createUserWithKeys();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::UNLISTED,
        ]);

        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->never())->method('deliverActivity');

        (new PushPostToMastodon($post->id))->handle($service);
    }

    public function test_push_post_skips_only_authenticated_post(): void
    {
        $user = $this->createUserWithKeys();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::ONLY_AUTHENTICATED,
        ]);

        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->never())->method('deliverActivity');

        (new PushPostToMastodon($post->id))->handle($service);
    }

    public function test_push_post_skips_when_user_has_no_followers(): void
    {
        $user = $this->createUserWithKeys();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PUBLIC,
        ]);

        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->never())->method('deliverActivity');

        (new PushPostToMastodon($post->id))->handle($service);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PushPostToMastodon – Zustellung
    // ──────────────────────────────────────────────────────────────────────────

    public function test_push_post_delivers_create_activity_to_each_follower_inbox(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PUBLIC,
        ]);

        $this->createFollower($user, 'https://server-a.example/users/bob', 'https://server-a.example/users/bob/inbox');
        $this->createFollower($user, 'https://server-b.example/users/carol', 'https://server-b.example/users/carol/inbox');

        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->exactly(2))->method('deliverActivity');

        (new PushPostToMastodon($post->id))->handle($service);
    }

    public function test_push_post_deduplicates_shared_inbox(): void
    {
        // Zwei Follower auf demselben Server → gleiche sharedInbox → nur 1 Zustellung
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PUBLIC,
        ]);

        $sharedInbox = 'https://mastodon.example/inbox';
        $this->createFollower($user, 'https://mastodon.example/users/bob', 'https://mastodon.example/users/bob/inbox', $sharedInbox);
        $this->createFollower($user, 'https://mastodon.example/users/carol', 'https://mastodon.example/users/carol/inbox', $sharedInbox);

        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->once())->method('deliverActivity');

        (new PushPostToMastodon($post->id))->handle($service);
    }

    public function test_push_post_prefers_shared_inbox_over_personal_inbox(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PUBLIC,
        ]);

        $sharedInbox = 'https://mastodon.example/inbox';
        $this->createFollower($user, 'https://mastodon.example/users/bob', 'https://mastodon.example/users/bob/inbox', $sharedInbox);

        $capturedInbox = null;
        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->once())
            ->method('deliverActivity')
            ->willReturnCallback(function ($userDto, $actorId, $inbox) use (&$capturedInbox) {
                $capturedInbox = $inbox;
            });

        (new PushPostToMastodon($post->id))->handle($service);

        $this->assertSame($sharedInbox, $capturedInbox);
    }

    public function test_push_post_falls_back_to_personal_inbox_when_no_shared_inbox(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create(['user_id' => $user->id, 'visibility' => Visibility::PUBLIC]);
        $personalInbox = 'https://remote.example/users/bob/inbox';

        $this->createFollower($user, 'https://remote.example/users/bob', $personalInbox, null);

        $capturedInbox = null;
        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->once())
            ->method('deliverActivity')
            ->willReturnCallback(function ($userDto, $actorId, $inbox) use (&$capturedInbox) {
                $capturedInbox = $inbox;
            });

        (new PushPostToMastodon($post->id))->handle($service);

        $this->assertSame($personalInbox, $capturedInbox);
    }

    public function test_push_post_create_activity_has_correct_structure(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PUBLIC,
            'body' => 'Hello Fediverse!',
        ]);

        $this->createFollower($user, 'https://remote.example/users/bob', 'https://remote.example/users/bob/inbox');

        $capturedActivity = null;
        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->once())
            ->method('deliverActivity')
            ->willReturnCallback(function ($userDto, $actorId, $inbox, $activity) use (&$capturedActivity) {
                $capturedActivity = $activity;
            });

        (new PushPostToMastodon($post->id))->handle($service);

        $this->assertNotNull($capturedActivity);
        $this->assertSame('Create', $capturedActivity['type']);
        $this->assertSame('Note', $capturedActivity['object']['type']);
        $this->assertStringContainsString('alice', $capturedActivity['actor']);
        $this->assertStringContainsString($post->id, $capturedActivity['id']);
        $this->assertStringContainsString($post->id, $capturedActivity['object']['id']);
        $this->assertContains('https://www.w3.org/ns/activitystreams#Public', $capturedActivity['to']);
        $this->assertArrayHasKey('@context', $capturedActivity);
    }

    public function test_push_post_activity_actor_matches_post_author(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create(['user_id' => $user->id, 'visibility' => Visibility::PUBLIC]);

        $this->createFollower($user, 'https://remote.example/users/bob', 'https://remote.example/users/bob/inbox');

        $capturedActivity = null;
        $service = $this->createMock(ActivityPubService::class);
        $service->method('deliverActivity')
            ->willReturnCallback(function ($u, $a, $i, $activity) use (&$capturedActivity) {
                $capturedActivity = $activity;
            });

        (new PushPostToMastodon($post->id))->handle($service);

        $expectedActorUrl = route('ap.actor', ['username' => 'alice']);
        $this->assertSame($expectedActorUrl, $capturedActivity['actor']);
        $this->assertSame($expectedActorUrl, $capturedActivity['object']['attributedTo']);
    }

    public function test_push_post_does_not_mix_up_followers_of_different_users(): void
    {
        $alice = $this->createUserWithKeys(['username' => 'alice']);
        $bob = $this->createUserWithKeys(['username' => 'bob']);
        $alicePost = Post::factory()->create(['user_id' => $alice->id, 'visibility' => Visibility::PUBLIC]);

        // Nur Bob hat Follower, Alice nicht
        $this->createFollower($bob, 'https://remote.example/users/carol', 'https://remote.example/users/carol/inbox');

        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->never())->method('deliverActivity');

        (new PushPostToMastodon($alicePost->id))->handle($service);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PushDeleteToMastodon – Retry-Konfiguration
    // ──────────────────────────────────────────────────────────────────────────

    public function test_push_delete_job_has_correct_retry_configuration(): void
    {
        $job = new PushDeleteToMastodon('post-id', 'user-id', 'username');

        $this->assertSame(3, $job->tries);
        $this->assertSame([30, 120, 600], $job->backoff);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PushDeleteToMastodon – Bedingungen zum Abbruch ohne Zustellung
    // ──────────────────────────────────────────────────────────────────────────

    public function test_push_delete_does_nothing_when_user_not_found(): void
    {
        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->never())->method('deliverActivity');

        (new PushDeleteToMastodon('post-id', '00000000-0000-7000-8000-000000000001', 'ghost'))->handle($service);
    }

    public function test_push_delete_skips_when_user_has_no_followers(): void
    {
        $user = $this->createUserWithKeys();

        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->never())->method('deliverActivity');

        (new PushDeleteToMastodon('some-post-id', $user->id, $user->username))->handle($service);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PushDeleteToMastodon – Zustellung
    // ──────────────────────────────────────────────────────────────────────────

    public function test_push_delete_delivers_delete_activity_to_each_follower(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);

        $this->createFollower($user, 'https://server-a.example/users/bob', 'https://server-a.example/users/bob/inbox');
        $this->createFollower($user, 'https://server-b.example/users/carol', 'https://server-b.example/users/carol/inbox');

        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->exactly(2))->method('deliverActivity');

        (new PushDeleteToMastodon('post-123', $user->id, $user->username))->handle($service);
    }

    public function test_push_delete_deduplicates_shared_inbox(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $sharedInbox = 'https://mastodon.example/inbox';

        $this->createFollower($user, 'https://mastodon.example/users/bob', 'https://mastodon.example/users/bob/inbox', $sharedInbox);
        $this->createFollower($user, 'https://mastodon.example/users/carol', 'https://mastodon.example/users/carol/inbox', $sharedInbox);

        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->once())->method('deliverActivity');

        (new PushDeleteToMastodon('post-123', $user->id, $user->username))->handle($service);
    }

    public function test_push_delete_activity_has_correct_structure(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $postId = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';

        $this->createFollower($user, 'https://remote.example/users/bob', 'https://remote.example/users/bob/inbox');

        $capturedActivity = null;
        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->once())
            ->method('deliverActivity')
            ->willReturnCallback(function ($userDto, $actorId, $inbox, $activity) use (&$capturedActivity) {
                $capturedActivity = $activity;
            });

        (new PushDeleteToMastodon($postId, $user->id, $user->username))->handle($service);

        $this->assertNotNull($capturedActivity);
        $this->assertSame('Delete', $capturedActivity['type']);
        $this->assertSame('Tombstone', $capturedActivity['object']['type']);
        $this->assertStringContainsString($postId, $capturedActivity['object']['id']);
        $this->assertStringContainsString('alice', $capturedActivity['actor']);
        $this->assertContains('https://www.w3.org/ns/activitystreams#Public', $capturedActivity['to']);
        $this->assertArrayHasKey('@context', $capturedActivity);
    }

    public function test_push_delete_tombstone_url_matches_post_object_route(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $postId = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';

        $this->createFollower($user, 'https://remote.example/users/bob', 'https://remote.example/users/bob/inbox');

        $capturedActivity = null;
        $service = $this->createMock(ActivityPubService::class);
        $service->method('deliverActivity')
            ->willReturnCallback(function ($u, $a, $i, $activity) use (&$capturedActivity) {
                $capturedActivity = $activity;
            });

        (new PushDeleteToMastodon($postId, $user->id, $user->username))->handle($service);

        $expectedObjectUrl = route('ap.post-object', ['id' => $postId]);
        $this->assertSame($expectedObjectUrl, $capturedActivity['object']['id']);
    }

    public function test_push_delete_actor_url_uses_provided_username(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $postId = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';

        $this->createFollower($user, 'https://remote.example/users/bob', 'https://remote.example/users/bob/inbox');

        $capturedActivity = null;
        $service = $this->createMock(ActivityPubService::class);
        $service->method('deliverActivity')
            ->willReturnCallback(function ($u, $a, $i, $activity) use (&$capturedActivity) {
                $capturedActivity = $activity;
            });

        (new PushDeleteToMastodon($postId, $user->id, $user->username))->handle($service);

        $this->assertSame(route('ap.actor', ['username' => 'alice']), $capturedActivity['actor']);
    }

    public function test_push_delete_only_delivers_to_followers_of_the_given_user(): void
    {
        $alice = $this->createUserWithKeys(['username' => 'alice']);
        $bob = $this->createUserWithKeys(['username' => 'bob']);

        // Nur Bob hat Follower
        $this->createFollower($bob, 'https://remote.example/users/carol', 'https://remote.example/users/carol/inbox');

        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->never())->method('deliverActivity');

        (new PushDeleteToMastodon('post-id', $alice->id, $alice->username))->handle($service);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ActivityPubService::deliverActivity – HTTP-Integration
    // (testet den echten Service mit Http::fake())
    // ──────────────────────────────────────────────────────────────────────────

    public function test_deliver_activity_sends_signed_post_request_to_inbox(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create(['user_id' => $user->id, 'visibility' => Visibility::PUBLIC]);

        $inboxUrl = 'https://remote.example/users/bob/inbox';
        $this->createFollower($user, 'https://remote.example/users/bob', $inboxUrl);

        Http::fake(['*' => Http::response('', 202)]);

        (new PushPostToMastodon($post->id))->handle(app(ActivityPubService::class));

        Http::assertSent(function ($request) use ($inboxUrl) {
            return $request->url() === $inboxUrl
                && $request->header('Signature') !== []
                && $request->header('Digest') !== []
                && str_contains($request->header('Content-Type')[0] ?? '', 'application/activity+json');
        });
    }

    public function test_deliver_activity_sends_create_activity_body_to_inbox(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PUBLIC,
            'body' => 'Test post body',
        ]);

        $inboxUrl = 'https://remote.example/users/bob/inbox';
        $this->createFollower($user, 'https://remote.example/users/bob', $inboxUrl);

        Http::fake(['*' => Http::response('', 202)]);

        (new PushPostToMastodon($post->id))->handle(app(ActivityPubService::class));

        Http::assertSent(function ($request) {
            $body = json_decode($request->body(), true);

            return isset($body['type']) && $body['type'] === 'Create'
                && isset($body['object']['type']) && $body['object']['type'] === 'Note';
        });
    }

    public function test_deliver_activity_sends_delete_activity_body_to_inbox(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $postId = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';

        $inboxUrl = 'https://remote.example/users/bob/inbox';
        $this->createFollower($user, 'https://remote.example/users/bob', $inboxUrl);

        Http::fake(['*' => Http::response('', 202)]);

        (new PushDeleteToMastodon($postId, $user->id, $user->username))->handle(app(ActivityPubService::class));

        Http::assertSent(function ($request) use ($inboxUrl) {
            $body = json_decode($request->body(), true);

            return $request->url() === $inboxUrl
                && isset($body['type']) && $body['type'] === 'Delete'
                && isset($body['object']['type']) && $body['object']['type'] === 'Tombstone';
        });
    }

    public function test_deliver_activity_throws_on_server_error(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create(['user_id' => $user->id, 'visibility' => Visibility::PUBLIC]);

        $this->createFollower($user, 'https://remote.example/users/bob', 'https://remote.example/users/bob/inbox');

        Http::fake(['*' => Http::response('Internal Server Error', 500)]);

        $this->expectException(\RuntimeException::class);

        (new PushPostToMastodon($post->id))->handle(app(ActivityPubService::class));
    }

    public function test_deliver_activity_signature_header_contains_key_id_for_actor(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create(['user_id' => $user->id, 'visibility' => Visibility::PUBLIC]);

        $this->createFollower($user, 'https://remote.example/users/bob', 'https://remote.example/users/bob/inbox');

        Http::fake(['*' => Http::response('', 202)]);

        (new PushPostToMastodon($post->id))->handle(app(ActivityPubService::class));

        $expectedKeyId = route('ap.actor', ['username' => 'alice']).'#main-key';

        Http::assertSent(function ($request) use ($expectedKeyId) {
            $signature = $request->header('Signature')[0] ?? '';

            return str_contains($signature, "keyId=\"{$expectedKeyId}\"");
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PushUpdateToMastodon – Retry-Konfiguration
    // ──────────────────────────────────────────────────────────────────────────

    public function test_push_update_job_has_correct_retry_configuration(): void
    {
        $job = new PushUpdateToMastodon('some-id');

        $this->assertSame(3, $job->tries);
        $this->assertSame([30, 120, 600], $job->backoff);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PushUpdateToMastodon – Bedingungen zum Abbruch ohne Zustellung
    // ──────────────────────────────────────────────────────────────────────────

    public function test_push_update_does_nothing_when_post_not_found(): void
    {
        Http::fake();

        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->never())->method('deliverActivity');

        (new PushUpdateToMastodon('00000000-0000-7000-8000-000000000000'))->handle($service);

        Http::assertNothingSent();
    }

    public function test_push_update_skips_private_post(): void
    {
        $user = $this->createUserWithKeys();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PRIVATE,
        ]);

        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->never())->method('deliverActivity');

        (new PushUpdateToMastodon($post->id))->handle($service);
    }

    public function test_push_update_skips_unlisted_post(): void
    {
        $user = $this->createUserWithKeys();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::UNLISTED,
        ]);

        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->never())->method('deliverActivity');

        (new PushUpdateToMastodon($post->id))->handle($service);
    }

    public function test_push_update_skips_only_authenticated_post(): void
    {
        $user = $this->createUserWithKeys();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::ONLY_AUTHENTICATED,
        ]);

        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->never())->method('deliverActivity');

        (new PushUpdateToMastodon($post->id))->handle($service);
    }

    public function test_push_update_skips_when_user_has_no_followers(): void
    {
        $user = $this->createUserWithKeys();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PUBLIC,
        ]);

        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->never())->method('deliverActivity');

        (new PushUpdateToMastodon($post->id))->handle($service);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PushUpdateToMastodon – Zustellung
    // ──────────────────────────────────────────────────────────────────────────

    public function test_push_update_delivers_update_activity_to_each_follower_inbox(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PUBLIC,
        ]);

        $this->createFollower($user, 'https://server-a.example/users/bob', 'https://server-a.example/users/bob/inbox');
        $this->createFollower($user, 'https://server-b.example/users/carol', 'https://server-b.example/users/carol/inbox');

        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->exactly(2))->method('deliverActivity');

        (new PushUpdateToMastodon($post->id))->handle($service);
    }

    public function test_push_update_deduplicates_shared_inbox(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PUBLIC,
        ]);

        $sharedInbox = 'https://mastodon.example/inbox';
        $this->createFollower($user, 'https://mastodon.example/users/bob', 'https://mastodon.example/users/bob/inbox', $sharedInbox);
        $this->createFollower($user, 'https://mastodon.example/users/carol', 'https://mastodon.example/users/carol/inbox', $sharedInbox);

        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->once())->method('deliverActivity');

        (new PushUpdateToMastodon($post->id))->handle($service);
    }

    public function test_push_update_prefers_shared_inbox_over_personal_inbox(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PUBLIC,
        ]);

        $sharedInbox = 'https://mastodon.example/inbox';
        $this->createFollower($user, 'https://mastodon.example/users/bob', 'https://mastodon.example/users/bob/inbox', $sharedInbox);

        $capturedInbox = null;
        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->once())
            ->method('deliverActivity')
            ->willReturnCallback(function ($userDto, $actorId, $inbox) use (&$capturedInbox) {
                $capturedInbox = $inbox;
            });

        (new PushUpdateToMastodon($post->id))->handle($service);

        $this->assertSame($sharedInbox, $capturedInbox);
    }

    public function test_push_update_falls_back_to_personal_inbox_when_no_shared_inbox(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create(['user_id' => $user->id, 'visibility' => Visibility::PUBLIC]);
        $personalInbox = 'https://remote.example/users/bob/inbox';

        $this->createFollower($user, 'https://remote.example/users/bob', $personalInbox, null);

        $capturedInbox = null;
        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->once())
            ->method('deliverActivity')
            ->willReturnCallback(function ($userDto, $actorId, $inbox) use (&$capturedInbox) {
                $capturedInbox = $inbox;
            });

        (new PushUpdateToMastodon($post->id))->handle($service);

        $this->assertSame($personalInbox, $capturedInbox);
    }

    public function test_push_update_activity_has_correct_structure(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PUBLIC,
            'body' => 'Updated post content!',
        ]);

        $this->createFollower($user, 'https://remote.example/users/bob', 'https://remote.example/users/bob/inbox');

        $capturedActivity = null;
        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->once())
            ->method('deliverActivity')
            ->willReturnCallback(function ($userDto, $actorId, $inbox, $activity) use (&$capturedActivity) {
                $capturedActivity = $activity;
            });

        (new PushUpdateToMastodon($post->id))->handle($service);

        $this->assertNotNull($capturedActivity);
        $this->assertSame('Update', $capturedActivity['type']);
        $this->assertSame('Note', $capturedActivity['object']['type']);
        $this->assertStringContainsString('alice', $capturedActivity['actor']);
        $this->assertStringContainsString($post->id, $capturedActivity['id']);
        $this->assertStringContainsString($post->id, $capturedActivity['object']['id']);
        $this->assertContains('https://www.w3.org/ns/activitystreams#Public', $capturedActivity['to']);
        $this->assertArrayHasKey('@context', $capturedActivity);
        $this->assertArrayHasKey('updated', $capturedActivity['object']);
    }

    public function test_push_update_activity_actor_matches_post_author(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create(['user_id' => $user->id, 'visibility' => Visibility::PUBLIC]);

        $this->createFollower($user, 'https://remote.example/users/bob', 'https://remote.example/users/bob/inbox');

        $capturedActivity = null;
        $service = $this->createMock(ActivityPubService::class);
        $service->method('deliverActivity')
            ->willReturnCallback(function ($u, $a, $i, $activity) use (&$capturedActivity) {
                $capturedActivity = $activity;
            });

        (new PushUpdateToMastodon($post->id))->handle($service);

        $expectedActorUrl = route('ap.actor', ['username' => 'alice']);
        $this->assertSame($expectedActorUrl, $capturedActivity['actor']);
        $this->assertSame($expectedActorUrl, $capturedActivity['object']['attributedTo']);
    }

    public function test_push_update_does_not_mix_up_followers_of_different_users(): void
    {
        $alice = $this->createUserWithKeys(['username' => 'alice']);
        $bob = $this->createUserWithKeys(['username' => 'bob']);
        $alicePost = Post::factory()->create(['user_id' => $alice->id, 'visibility' => Visibility::PUBLIC]);

        // Nur Bob hat Follower, Alice nicht
        $this->createFollower($bob, 'https://remote.example/users/carol', 'https://remote.example/users/carol/inbox');

        $service = $this->createMock(ActivityPubService::class);
        $service->expects($this->never())->method('deliverActivity');

        (new PushUpdateToMastodon($alicePost->id))->handle($service);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PushUpdateToMastodon – HTTP-Integration
    // ──────────────────────────────────────────────────────────────────────────

    public function test_deliver_activity_sends_update_activity_body_to_inbox(): void
    {
        $user = $this->createUserWithKeys(['username' => 'alice']);
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'visibility' => Visibility::PUBLIC,
            'body' => 'Updated post body',
        ]);

        $inboxUrl = 'https://remote.example/users/bob/inbox';
        $this->createFollower($user, 'https://remote.example/users/bob', $inboxUrl);

        Http::fake(['*' => Http::response('', 202)]);

        (new PushUpdateToMastodon($post->id))->handle(app(ActivityPubService::class));

        Http::assertSent(function ($request) use ($inboxUrl) {
            $body = json_decode($request->body(), true);

            return $request->url() === $inboxUrl
                && isset($body['type']) && $body['type'] === 'Update'
                && isset($body['object']['type']) && $body['object']['type'] === 'Note';
        });
    }
}
