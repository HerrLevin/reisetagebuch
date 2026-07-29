<?php

namespace Tests\Feature\Jobs;

use App\Jobs\FetchRemoteActorAvatar;
use App\Models\ActivityPubActor;
use App\Services\ActivityPubUrlGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FetchRemoteActorAvatarTest extends TestCase
{
    use RefreshDatabase;

    private function bypassUrlGuard(): void
    {
        $this->app->bind(ActivityPubUrlGuard::class, fn () => new class extends ActivityPubUrlGuard
        {
            public function assertSafe(string $url): void {}
        });
    }

    private function fakePng(): string
    {
        $image = imagecreatetruecolor(1, 1);
        ob_start();
        imagepng($image);
        $data = ob_get_clean();
        imagedestroy($image);

        return $data;
    }

    public function test_saves_a_valid_image_whose_content_matches_its_declared_type(): void
    {
        $this->bypassUrlGuard();
        Storage::fake('public');

        $actor = ActivityPubActor::create([
            'actor_uri' => 'https://remote.example/users/bob',
            'inbox_url' => 'https://remote.example/users/bob/inbox',
            'remote_icon_url' => 'https://remote.example/avatar.png',
        ]);

        Http::fake([
            '*' => Http::response($this->fakePng(), 200, ['Content-Type' => 'image/png', 'ETag' => 'abc']),
        ]);

        (new FetchRemoteActorAvatar($actor->id))->handle(app(ActivityPubUrlGuard::class));

        $actor->refresh();
        $this->assertNotNull($actor->local_icon_path);
        Storage::disk('public')->assertExists($actor->local_icon_path);
        $this->assertSame('image/png', $actor->icon_mime_type);
        $this->assertSame('abc', $actor->icon_etag);
    }

    public function test_rejects_a_response_whose_body_is_not_actually_an_image_despite_an_image_content_type(): void
    {
        $this->bypassUrlGuard();
        Storage::fake('public');

        $actor = ActivityPubActor::create([
            'actor_uri' => 'https://remote.example/users/mallory',
            'inbox_url' => 'https://remote.example/users/mallory/inbox',
            'remote_icon_url' => 'https://remote.example/avatar.png',
        ]);

        Http::fake([
            '*' => Http::response('<?php echo "not an image"; ?>', 200, ['Content-Type' => 'image/png']),
        ]);

        (new FetchRemoteActorAvatar($actor->id))->handle(app(ActivityPubUrlGuard::class));

        $actor->refresh();
        $this->assertNull($actor->local_icon_path);
        $this->assertNull($actor->icon_mime_type);
        Storage::disk('public')->assertDirectoryEmpty('ap-avatars');
    }

    public function test_keeps_the_previously_stored_avatar_when_a_refetch_returns_invalid_content(): void
    {
        $this->bypassUrlGuard();
        Storage::fake('public');

        $disk = Storage::disk('public');
        $disk->put('ap-avatars/existing.png', $this->fakePng());

        $actor = ActivityPubActor::create([
            'actor_uri' => 'https://remote.example/users/carol',
            'inbox_url' => 'https://remote.example/users/carol/inbox',
            'remote_icon_url' => 'https://remote.example/avatar.png',
            'local_icon_path' => 'ap-avatars/existing.png',
            'icon_mime_type' => 'image/png',
        ]);

        Http::fake([
            '*' => Http::response('not an image', 200, ['Content-Type' => 'image/png']),
        ]);

        (new FetchRemoteActorAvatar($actor->id))->handle(app(ActivityPubUrlGuard::class));

        $actor->refresh();
        $this->assertSame('ap-avatars/existing.png', $actor->local_icon_path);
        $disk->assertExists('ap-avatars/existing.png');
    }

    public function test_strips_a_malicious_payload_appended_after_a_valid_image_header(): void
    {
        $this->bypassUrlGuard();
        Storage::fake('public');

        $actor = ActivityPubActor::create([
            'actor_uri' => 'https://remote.example/users/eve',
            'inbox_url' => 'https://remote.example/users/eve/inbox',
            'remote_icon_url' => 'https://remote.example/avatar.png',
        ]);

        $polyglot = $this->fakePng().'<?php system($_GET["cmd"]); ?>';

        Http::fake([
            '*' => Http::response($polyglot, 200, ['Content-Type' => 'image/png']),
        ]);

        (new FetchRemoteActorAvatar($actor->id))->handle(app(ActivityPubUrlGuard::class));

        $actor->refresh();
        $this->assertNotNull($actor->local_icon_path);
        $stored = Storage::disk('public')->get($actor->local_icon_path);
        $this->assertStringNotContainsString('<?php', $stored);
    }

    public function test_rejects_images_exceeding_the_maximum_allowed_dimensions(): void
    {
        $this->bypassUrlGuard();
        Storage::fake('public');

        $actor = ActivityPubActor::create([
            'actor_uri' => 'https://remote.example/users/dave',
            'inbox_url' => 'https://remote.example/users/dave/inbox',
            'remote_icon_url' => 'https://remote.example/avatar.png',
        ]);

        $oversized = imagecreatetruecolor(4001, 1);
        ob_start();
        imagepng($oversized);
        $data = ob_get_clean();
        imagedestroy($oversized);

        Http::fake([
            '*' => Http::response($data, 200, ['Content-Type' => 'image/png']),
        ]);

        (new FetchRemoteActorAvatar($actor->id))->handle(app(ActivityPubUrlGuard::class));

        $actor->refresh();
        $this->assertNull($actor->local_icon_path);
        Storage::disk('public')->assertDirectoryEmpty('ap-avatars');
    }
}
