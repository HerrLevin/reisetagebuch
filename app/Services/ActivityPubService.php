<?php

namespace App\Services;

use App\Enums\Visibility;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ActivityPubService
{
    public function signAndSend(string $inboxUrl, array $activity, User $sender): void
    {
        $body = json_encode($activity);
        $parsed = parse_url($inboxUrl);
        $host = $parsed['host'];
        $path = $parsed['path'] ?? '/';
        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $digest = 'SHA-256='.base64_encode(hash('sha256', $body, true));

        $signingString = "(request-target): post {$path}\nhost: {$host}\ndate: {$date}\ndigest: {$digest}";

        $privateKey = openssl_pkey_get_private($sender->private_key);
        openssl_sign($signingString, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $signatureB64 = base64_encode($signature);

        $actorUrl = url("/ap/users/{$sender->username}");
        $signatureHeader = "keyId=\"{$actorUrl}#main-key\",algorithm=\"rsa-sha256\",headers=\"(request-target) host date digest\",signature=\"{$signatureB64}\"";

        try {
            Http::withHeaders([
                'Host' => $host,
                'Date' => $date,
                'Digest' => $digest,
                'Signature' => $signatureHeader,
                'Content-Type' => 'application/activity+json',
                'Accept' => 'application/activity+json',
            ])->withBody($body, 'application/activity+json')
                ->timeout(30)
                ->post($inboxUrl);
        } catch (\Exception $e) {
            Log::error("ActivityPub delivery failed to {$inboxUrl}: {$e->getMessage()}");
            throw $e;
        }
    }

    public function verifyHttpSignature(Request $request): bool
    {
        $signatureHeader = $request->header('Signature');
        if (! $signatureHeader) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $part) {
            $part = trim($part);
            if (preg_match('/^(\w+)="(.*)"$/s', $part, $matches)) {
                $parts[$matches[1]] = $matches[2];
            }
        }

        if (! isset($parts['keyId'], $parts['signature'], $parts['headers'])) {
            return false;
        }

        $actor = $this->fetchRemoteActor($parts['keyId']);
        if (! $actor || ! isset($actor['publicKey']['publicKeyPem'])) {
            return false;
        }

        $headers = explode(' ', $parts['headers']);
        $signingParts = [];
        foreach ($headers as $header) {
            if ($header === '(request-target)') {
                $signingParts[] = '(request-target): '.strtolower($request->method()).' '.$request->getRequestUri();
            } elseif ($header === 'host') {
                $signingParts[] = 'host: '.$request->header('Host');
            } elseif ($header === 'date') {
                $signingParts[] = 'date: '.$request->header('Date');
            } elseif ($header === 'digest') {
                $signingParts[] = 'digest: '.$request->header('Digest');
            } else {
                $signingParts[] = $header.': '.$request->header(str_replace('-', ' ', ucwords($header, '-')));
            }
        }

        $signingString = implode("\n", $signingParts);
        $publicKey = openssl_pkey_get_public($actor['publicKey']['publicKeyPem']);
        $signature = base64_decode($parts['signature']);

        return openssl_verify($signingString, $signature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }

    public function fetchRemoteActor(string $actorUrl): ?array
    {
        // The keyId may have a fragment like #main-key, strip it for the actor URL
        $actorUrl = preg_replace('/#.*$/', '', $actorUrl);

        return Cache::remember("activitypub:actor:{$actorUrl}", 86400, function () use ($actorUrl) {
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/activity+json',
                ])->timeout(15)->get($actorUrl);

                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                Log::warning("Failed to fetch remote actor {$actorUrl}: {$e->getMessage()}");
            }

            return null;
        });
    }

    public function buildNote(Post $post): array
    {
        $post->loadMissing(['user', 'locationPost.location', 'transportPost.originStop.location', 'transportPost.destinationStop.location']);

        $user = $post->user;
        $actorUrl = url("/ap/users/{$user->username}");
        $noteUrl = url("/ap/posts/{$post->id}");
        $postWebUrl = url("/@{$user->username}/posts/{$post->id}");

        $contentParts = [];
        if ($post->body) {
            $contentParts[] = '<p>'.e($post->body).'</p>';
        }

        if ($post->locationPost && $post->locationPost->location) {
            $locationName = $post->locationPost->location->name ?? '';
            if ($locationName) {
                $contentParts[] = '<p>📍 '.e($locationName).'</p>';
            }
        }

        if ($post->transportPost) {
            $origin = $post->transportPost->originStop?->location?->name ?? '';
            $destination = $post->transportPost->destinationStop?->location?->name ?? '';
            if ($origin && $destination) {
                $contentParts[] = '<p>🚆 '.e($origin).' → '.e($destination).'</p>';
            }
        }

        $contentParts[] = '<p><a href="'.e($postWebUrl).'">'.e($postWebUrl).'</a></p>';

        $content = implode("\n", $contentParts);

        $followersUrl = url("/ap/users/{$user->username}/followers");
        $public = 'https://www.w3.org/ns/activitystreams#Public';

        if ($post->visibility === Visibility::PUBLIC) {
            $to = [$public];
            $cc = [$followersUrl];
        } else {
            $to = [$followersUrl];
            $cc = [$public];
        }

        return [
            'id' => $noteUrl,
            'type' => 'Note',
            'attributedTo' => $actorUrl,
            'content' => $content,
            'url' => $postWebUrl,
            'published' => $post->published_at->toIso8601String(),
            'to' => $to,
            'cc' => $cc,
        ];
    }

    public function buildCreateActivity(Post $post): array
    {
        $note = $this->buildNote($post);
        $user = $post->user;

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => url("/ap/posts/{$post->id}").'#create',
            'type' => 'Create',
            'actor' => url("/ap/users/{$user->username}"),
            'published' => $post->published_at->toIso8601String(),
            'to' => $note['to'],
            'cc' => $note['cc'],
            'object' => $note,
        ];
    }

    public function buildUpdateActivity(Post $post): array
    {
        $note = $this->buildNote($post);
        $user = $post->user;

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => url("/ap/posts/{$post->id}").'#update-'.now()->timestamp,
            'type' => 'Update',
            'actor' => url("/ap/users/{$user->username}"),
            'to' => $note['to'],
            'cc' => $note['cc'],
            'object' => $note,
        ];
    }

    public function buildDeleteActivity(Post $post): array
    {
        $user = $post->user;
        $followersUrl = url("/ap/users/{$user->username}/followers");

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => url("/ap/posts/{$post->id}").'#delete',
            'type' => 'Delete',
            'actor' => url("/ap/users/{$user->username}"),
            'to' => ['https://www.w3.org/ns/activitystreams#Public'],
            'cc' => [$followersUrl],
            'object' => [
                'id' => url("/ap/posts/{$post->id}"),
                'type' => 'Tombstone',
            ],
        ];
    }
}
