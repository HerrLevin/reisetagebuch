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

    private const array ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    private const int MAX_DIMENSION = 4000;

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

        $body = $response->body();
        $imageInfo = @getimagesizefromstring($body);
        $mimeType = $imageInfo['mime'] ?? null;
        $extension = $mimeType ? (self::ALLOWED_MIME_TYPES[$mimeType] ?? null) : null;

        if (! $extension) {
            Log::warning('FetchRemoteActorAvatar: Response body is not a valid supported image', [
                'actorId' => $this->actorId,
                'url' => $actor->remote_icon_url,
                'contentType' => $response->header('Content-Type'),
                'detectedMimeType' => $mimeType,
            ]);

            return;
        }

        if ($imageInfo[0] > self::MAX_DIMENSION || $imageInfo[1] > self::MAX_DIMENSION) {
            Log::warning('FetchRemoteActorAvatar: Image dimensions exceed the allowed maximum', [
                'actorId' => $this->actorId,
                'url' => $actor->remote_icon_url,
                'width' => $imageInfo[0],
                'height' => $imageInfo[1],
            ]);

            return;
        }

        // Fully decode and re-encode the image ourselves rather than persisting the
        // remote bytes verbatim. This strips any payload smuggled past the header
        // (polyglot files, malicious metadata) since the output only ever contains
        // pixel data that GD itself produced.
        $reencoded = $this->reencode($body, $mimeType);

        if ($reencoded === null) {
            Log::warning('FetchRemoteActorAvatar: Image could not be decoded', [
                'actorId' => $this->actorId,
                'url' => $actor->remote_icon_url,
                'mimeType' => $mimeType,
            ]);

            return;
        }

        $disk = Storage::disk('public');

        // Delete old file if it exists
        if ($actor->local_icon_path && $disk->exists($actor->local_icon_path)) {
            $disk->delete($actor->local_icon_path);
        }

        $filename = 'ap-avatars/'.$actor->id.'.'.$extension;
        $disk->put($filename, $reencoded);

        $actor->update([
            'local_icon_path' => $filename,
            'icon_mime_type' => $mimeType,
            'icon_etag' => $response->header('ETag'),
            'icon_fetched_at' => now(),
        ]);
    }

    private function reencode(string $body, string $mimeType): ?string
    {
        $image = @imagecreatefromstring($body);

        if ($image === false) {
            return null;
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        ob_start();
        $encoded = match ($mimeType) {
            'image/jpeg' => imagejpeg($image, quality: 85),
            'image/png' => imagepng($image),
            'image/gif' => imagegif($image),
            'image/webp' => imagewebp($image),
            default => false,
        };
        $data = ob_get_clean();

        imagedestroy($image);

        return $encoded ? $data : null;
    }
}
