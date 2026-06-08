<?php

namespace App\Services;

use App\Http\Resources\UserDto;
use App\Jobs\FetchRemoteActorAvatar;
use App\Models\ActivityPubActor;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use JetBrains\PhpStorm\ArrayShape;

class ActivityPubService
{
    #[ArrayShape(['inbox' => 'string|null', 'sharedInbox' => 'string|null'])]
    public function getInbox(string $followerActorId): ?array
    {
        $profile = $this->getActorProfile($followerActorId);
        if ($profile === null) {
            return null;
        }

        return [
            'inbox' => $profile['inbox'],
            'sharedInbox' => $profile['sharedInbox'],
        ];
    }

    #[ArrayShape([
        'inbox' => 'string|null',
        'sharedInbox' => 'string|null',
        'preferredUsername' => 'string|null',
        'name' => 'string|null',
        'iconUrl' => 'string|null',
        'url' => 'string|null',
    ])]
    public function getActorProfile(string $actorId): ?array
    {
        $actor = null;
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/activity+json',
            ])->get($actorId);

            if ($response->successful()) {
                $actor = $response->json();
                $inbox = $actor['inbox'] ?? null;
                $endpoints = $actor['endpoints'] ?? [];
                $sharedInbox = $endpoints['sharedInbox'] ?? null;
                if ($inbox) {
                    return [
                        'inbox' => $inbox,
                        'sharedInbox' => $sharedInbox,
                        'preferredUsername' => $actor['preferredUsername'] ?? null,
                        'name' => $actor['name'] ?? null,
                        'iconUrl' => $actor['icon']['url'] ?? null,
                        'url' => $actor['url'] ?? null,
                    ];
                }
            }
            Log::warning('No inbox found for actor: '.$actorId, ['actor' => $actor, 'response' => $response->body()]);
        } catch (\Exception $e) {
            Log::error('Error fetching actor: '.$actorId.' Error: '.$e->getMessage());
        }

        return null;
    }

    public function resolveActor(string $actorUri): ?ActivityPubActor
    {
        $profile = $this->getActorProfile($actorUri);
        if ($profile === null) {
            return null;
        }

        $actor = ActivityPubActor::updateOrCreate(
            ['actor_uri' => $actorUri],
            [
                'preferred_username' => $profile['preferredUsername'],
                'display_name' => $profile['name'],
                'profile_url' => $profile['url'],
                'inbox_url' => $profile['inbox'],
                'shared_inbox_url' => $profile['sharedInbox'],
                'remote_icon_url' => $profile['iconUrl'],
                'profile_fetched_at' => now(),
            ]
        );

        $remoteIconUrl = $profile['iconUrl'];

        if ($remoteIconUrl === null && $actor->local_icon_path) {
            // Remote actor removed their avatar — delete local copy
            Storage::disk('public')->delete($actor->local_icon_path);
            $actor->update([
                'local_icon_path' => null,
                'icon_mime_type' => null,
                'icon_etag' => null,
                'icon_fetched_at' => null,
            ]);
        } elseif ($remoteIconUrl !== null && ($actor->wasRecentlyCreated || $actor->local_icon_path === null || $actor->wasChanged('remote_icon_url'))) {
            FetchRemoteActorAvatar::dispatch($actor->id);
        }

        return $actor;
    }

    public function deliverActivity(UserDto $user, string $followerActorId, ?string $inbox, array $activity): void
    {
        Log::info('Deliver Activity for actor: '.$followerActorId);
        // Fetch the follower's actor to get their inbox
        $inbox = $inbox ?? $this->getInbox($followerActorId)['inbox'] ?? null;

        if (! $inbox) {
            Log::warning('No inbox URL to deliver activity', ['actor' => $followerActorId]);

            return;
        }

        // Prepare the request
        $body = json_encode($activity, JSON_UNESCAPED_SLASHES);
        $date = now()->toRfc7231String();
        $digest = 'SHA-256='.base64_encode(hash('sha256', $body, true));
        $host = parse_url($inbox, PHP_URL_HOST);

        // Create signature
        $signature = $this->createSignature($user, 'POST', parse_url($inbox, PHP_URL_PATH), $host, $date, $digest);

        // Send to inbox
        $data = Http::withHeaders([
            'Content-Type' => 'application/activity+json',
            'Date' => $date,
            'Digest' => $digest,
            'Signature' => $signature,
        ])->withBody($body, 'application/activity+json')->post($inbox);
        Log::info('Delivered Activity to inbox: '.$inbox.' Response status: '.$data->status(), [
            'body' => $body,
            'Content-Type' => 'application/activity+json',
            'Date' => $date,
            'Digest' => $digest,
            'Signature' => $signature,
        ]);
        Log::info($data->body());

        if ($data->serverError()) {
            throw new \RuntimeException('Failed to deliver activity to '.$inbox.': '.$data->status());
        }
    }

    private function createSignature(UserDto $user, string $method, string $path, string $host, string $date, string $digest): string
    {
        $keyId = route('ap.actor', ['username' => $user->username]).'#main-key';
        $headers = '(request-target) host date digest';
        $stringToSign = '(request-target): '.strtolower($method)." {$path}\nhost: {$host}\ndate: {$date}\ndigest: {$digest}";

        $userModel = User::whereId($user->id)->first();

        $signature = '';
        openssl_sign($stringToSign, $signature, $userModel->private_key, OPENSSL_ALGO_SHA256);

        return 'keyId="'.$keyId.'",algorithm="rsa-sha256",headers="'.$headers.'",signature="'.base64_encode($signature).'"';
    }
}
