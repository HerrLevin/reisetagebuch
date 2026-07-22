<?php

namespace Tests\Feature\Controllers\Api;

use App\Jobs\SendFollowToRemoteActor;
use App\Jobs\SendUndoFollowToRemoteActor;
use App\Models\ActivityPubActor;
use App\Models\ActivityPubRemoteFollow;
use App\Models\User;
use App\Services\ActivityPubUrlGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Passport;
use ReflectionProperty;
use Tests\TestCase;

class ActivityPubFederationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Diese Tests mocken alle ausgehenden HTTP-Aufrufe über Http::fake() mit
        // Fake-Domains wie "remote.example". ActivityPubUrlGuard würde dafür
        // trotzdem eine echte DNS-Auflösung versuchen, daher wird sie hier durch
        // einen No-Op ersetzt (Fix 3 selbst ist bereits durch ActivityPubUrlGuardTest
        // abgedeckt).
        $this->app->bind(ActivityPubUrlGuard::class, fn () => new class extends ActivityPubUrlGuard
        {
            public function assertSafe(string $url): void {}
        });
    }

    private function fakeRemoteActor(string $actorId, string $handle, array $profileOverrides = []): void
    {
        [$username, $domain] = explode('@', ltrim($handle, '@'), 2);
        $resource = urlencode("acct:{$username}@{$domain}");
        $webfingerUrl = "https://{$domain}/.well-known/webfinger?resource={$resource}";

        Http::fake([
            $webfingerUrl => Http::response([
                'subject' => "acct:{$username}@{$domain}",
                'links' => [
                    ['rel' => 'self', 'type' => 'application/activity+json', 'href' => $actorId],
                ],
            ]),
            $actorId => Http::response(array_merge([
                'id' => $actorId,
                'type' => 'Person',
                'preferredUsername' => $username,
                'name' => 'Remote User',
                'summary' => 'Hello world',
                'inbox' => "{$actorId}/inbox",
                'endpoints' => ['sharedInbox' => "https://{$domain}/inbox"],
                'icon' => ['url' => "https://{$domain}/avatar.png"],
                'url' => $actorId,
            ], $profileOverrides)),
        ]);
    }

    private function jobProperty(object $job, string $name): mixed
    {
        return new ReflectionProperty($job, $name)->getValue($job);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Authentifizierung
    // ──────────────────────────────────────────────────────────────────────────

    public function test_resolve_requires_authentication(): void
    {
        $this->getJson('/api/activitypub/resolve?handle=bob@remote.example')->assertStatus(401);
    }

    public function test_following_requires_authentication(): void
    {
        $this->getJson('/api/activitypub/following')->assertStatus(401);
    }

    public function test_follow_requires_authentication(): void
    {
        $this->postJson('/api/activitypub/follow', ['actor_id' => 'https://remote.example/users/bob'])
            ->assertStatus(401);
    }

    public function test_unfollow_requires_authentication(): void
    {
        $this->deleteJson('/api/activitypub/follow', ['actor_id' => 'https://remote.example/users/bob'])
            ->assertStatus(401);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/activitypub/resolve
    // ──────────────────────────────────────────────────────────────────────────

    public function test_resolve_returns_actor_profile_for_valid_handle(): void
    {
        Passport::actingAs(User::factory()->create());

        $actorId = 'https://remote.example/users/bob';
        $this->fakeRemoteActor($actorId, 'bob@remote.example');

        $response = $this->getJson('/api/activitypub/resolve?handle=bob@remote.example');

        $response->assertOk()->assertJson([
            'actor_id' => $actorId,
            'display_name' => 'Remote User',
            'preferred_username' => 'bob',
            'summary' => 'Hello world',
        ]);
    }

    public function test_resolve_rejects_handle_without_domain(): void
    {
        Passport::actingAs(User::factory()->create());

        $this->getJson('/api/activitypub/resolve?handle=bob')->assertStatus(422);
    }

    public function test_resolve_returns_404_when_webfinger_cannot_resolve(): void
    {
        Passport::actingAs(User::factory()->create());

        $resource = urlencode('acct:ghost@remote.example');
        Http::fake([
            "https://remote.example/.well-known/webfinger?resource={$resource}" => Http::response([], 404),
        ]);

        $this->getJson('/api/activitypub/resolve?handle=ghost@remote.example')->assertStatus(404);
    }

    public function test_resolve_strips_script_tag_from_summary(): void
    {
        Passport::actingAs(User::factory()->create());

        $actorId = 'https://remote.example/users/mallory';
        $this->fakeRemoteActor($actorId, 'mallory@remote.example', [
            'summary' => '<p>Hi</p><script>alert(1)</script>',
        ]);

        $response = $this->getJson('/api/activitypub/resolve?handle=mallory@remote.example');

        $response->assertOk();
        $this->assertStringNotContainsString('<script', $response->json('summary'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /api/activitypub/follow
    // ──────────────────────────────────────────────────────────────────────────

    public function test_follow_requires_a_valid_url_actor_id(): void
    {
        Passport::actingAs(User::factory()->create());

        $this->postJson('/api/activitypub/follow', ['actor_id' => 'not-a-url'])->assertStatus(422);
        $this->postJson('/api/activitypub/follow', [])->assertStatus(422);
    }

    public function test_follow_creates_remote_follow_and_dispatches_job(): void
    {
        Bus::fake();
        $user = User::factory()->create();
        Passport::actingAs($user);

        $actorId = 'https://remote.example/users/bob';
        $this->fakeRemoteActor($actorId, 'bob@remote.example');

        $this->postJson('/api/activitypub/follow', ['actor_id' => $actorId])->assertStatus(204);

        $this->assertDatabaseHas('activity_pub_remote_follows', [
            'local_user_id' => $user->id,
            'remote_actor_id' => $actorId,
        ]);
        Bus::assertDispatched(
            SendFollowToRemoteActor::class,
            fn ($job) => $this->jobProperty($job, 'userId') === $user->id
                && $this->jobProperty($job, 'remoteActorId') === $actorId
        );
    }

    public function test_follow_is_idempotent(): void
    {
        Bus::fake();
        Passport::actingAs(User::factory()->create());

        $actorId = 'https://remote.example/users/bob';
        $this->fakeRemoteActor($actorId, 'bob@remote.example');

        $this->postJson('/api/activitypub/follow', ['actor_id' => $actorId])->assertStatus(204);
        $this->postJson('/api/activitypub/follow', ['actor_id' => $actorId])->assertStatus(204);

        $this->assertDatabaseCount('activity_pub_remote_follows', 1);
    }

    public function test_follow_returns_422_when_remote_actor_unreachable(): void
    {
        Passport::actingAs(User::factory()->create());

        $actorId = 'https://remote.example/users/ghost';
        Http::fake([$actorId => Http::response([], 500)]);

        $response = $this->postJson('/api/activitypub/follow', ['actor_id' => $actorId]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('activity_pub_remote_follows', ['remote_actor_id' => $actorId]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DELETE /api/activitypub/follow
    // ──────────────────────────────────────────────────────────────────────────

    public function test_unfollow_is_a_noop_when_not_following(): void
    {
        Bus::fake();
        Passport::actingAs(User::factory()->create());

        $this->deleteJson('/api/activitypub/follow', ['actor_id' => 'https://remote.example/users/bob'])
            ->assertStatus(204);

        Bus::assertNotDispatched(SendUndoFollowToRemoteActor::class);
    }

    public function test_unfollow_deletes_follow_and_dispatches_job_with_stored_activity_id(): void
    {
        Bus::fake();
        $user = User::factory()->create();
        $actorId = 'https://remote.example/users/bob';

        $follow = ActivityPubRemoteFollow::create([
            'local_user_id' => $user->id,
            'remote_actor_id' => $actorId,
            'remote_actor_inbox_url' => "{$actorId}/inbox",
            'remote_actor_shared_inbox_url' => null,
            'follow_activity_id' => 'https://example.test/users/'.$user->username.'#follows/abc',
            'state' => 'accepted',
        ]);

        Passport::actingAs($user);

        $this->deleteJson('/api/activitypub/follow', ['actor_id' => $actorId])->assertStatus(204);

        $this->assertDatabaseMissing('activity_pub_remote_follows', ['id' => $follow->id]);
        Bus::assertDispatched(
            SendUndoFollowToRemoteActor::class,
            fn ($job) => $this->jobProperty($job, 'followActivityId') === $follow->follow_activity_id
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /api/activitypub/following
    // ──────────────────────────────────────────────────────────────────────────

    public function test_following_returns_only_current_users_follows(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $actor = ActivityPubActor::factory()->create();
        ActivityPubRemoteFollow::create([
            'local_user_id' => $user->id,
            'remote_actor_id' => $actor->actor_uri,
            'remote_actor_inbox_url' => $actor->inbox_url,
            'remote_actor_shared_inbox_url' => $actor->shared_inbox_url,
            'follow_activity_id' => 'https://example.test/follows/mine',
            'state' => 'accepted',
        ]);

        $otherActor = ActivityPubActor::factory()->create();
        ActivityPubRemoteFollow::create([
            'local_user_id' => $otherUser->id,
            'remote_actor_id' => $otherActor->actor_uri,
            'remote_actor_inbox_url' => $otherActor->inbox_url,
            'remote_actor_shared_inbox_url' => $otherActor->shared_inbox_url,
            'follow_activity_id' => 'https://example.test/follows/other',
            'state' => 'accepted',
        ]);

        Passport::actingAs($user);

        $response = $this->getJson('/api/activitypub/following');

        $response->assertOk()->assertJsonCount(1);
        $this->assertSame($actor->actor_uri, $response->json('0.actor_id'));
    }
}
