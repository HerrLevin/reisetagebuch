<?php

namespace App\Jobs;

use App\Models\ActivityPubActor;
use App\Services\ActivityPubUrlGuard;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FetchRemoteActorAvatar implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [30, 120, 600];

    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    public function __construct(
        private readonly string $actorId
    ) {}

    public function handle(ActivityPubUrlGuard $urlGuard): void
    {
        $actor = ActivityPubActor::find($this->actorId);
        if (! $actor || ! $actor->remote_icon_url) {
            return;
        }

        $headers = [];
        if ($actor->icon_etag) {
            $headers['If-None-Match'] = $actor->icon_etag;
        }

        try {
            $urlGuard->assertSafe($actor->remote_icon_url);

            $response = Http::withHeaders($headers)
                ->withOptions(['allow_redirects' => false])
                ->timeout(15)
                ->get($actor->remote_icon_url);
        } catch (\Exception $e) {
            Log::warning('FetchRemoteActorAvatar: HTTP request failed', [
                'actorId' => $this->actorId,
                'url' => $actor->remote_icon_url,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        if ($response->status() === 304) {
            $actor->update(['icon_fetched_at' => now()]);

            return;
        }

        if (! $response->successful()) {
            Log::warning('FetchRemoteActorAvatar: Non-successful response', [
                'actorId' => $this->actorId,
                'url' => $actor->remote_icon_url,
                'status' => $response->status(),
            ]);
            throw new \RuntimeException('Failed to fetch avatar: HTTP '.$response->status());
        }

        $contentType = $response->header('Content-Type');
        $mimeType = strtok($contentType, ';');
        $extension = self::ALLOWED_MIME_TYPES[$mimeType] ?? null;

        if (! $extension) {
            Log::warning('FetchRemoteActorAvatar: Unsupported MIME type', [
                'actorId' => $this->actorId,
                'mimeType' => $contentType,
            ]);

            return;
        }

        $disk = Storage::disk('public');

        // Delete old file if it exists
        if ($actor->local_icon_path && $disk->exists($actor->local_icon_path)) {
            $disk->delete($actor->local_icon_path);
        }

        $filename = 'ap-avatars/'.$actor->id.'.'.$extension;
        $disk->put($filename, $response->body());

        $actor->update([
            'local_icon_path' => $filename,
            'icon_mime_type' => $mimeType,
            'icon_etag' => $response->header('ETag'),
            'icon_fetched_at' => now(),
        ]);
    }
}
