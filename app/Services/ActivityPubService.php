<?php

namespace App\Services;

use App\Http\Resources\UserDto;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;

class ActivityPubService
{
    #[ArrayShape(['inbox' => 'string|null', 'sharedInbox' => 'string|null'])]
    public function getInbox(string $followerActorId): ?array
    {
        $followerActor = null;
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/activity+json',
            ])->get($followerActorId);

            if ($response->successful()) {
                $followerActor = $response->json();
                $inbox = $followerActor['inbox'] ?? null;
                $endpoints = $followerActor['endpoints'] ?? [];
                $sharedInbox = $endpoints['sharedInbox'] ?? null;
                if ($inbox) {
                    return [
                        'inbox' => $inbox,
                        'sharedInbox' => $sharedInbox,
                    ];
                }
            }
            Log::warning('No inbox found for actor: '.$followerActorId, ['followerActor' => $followerActor, 'response' => $response->body()]);
        } catch (\Exception $e) {
            Log::error('Error fetching actor: '.$followerActorId.' Error: '.$e->getMessage());
        }

        return null; // Error fetching
    }

    public function deliverActivity(UserDto $user, string $followerActorId, ?string $inbox, array $activity): void
    {
        Log::info('Deliver Activity for actor: '.$followerActorId);
        // Fetch the follower's actor to get their inbox
        $inbox = $inbox ?? $this->getInbox($followerActorId)['inbox'] ?? null;

        // Prepare the request
        $body = json_encode($activity);
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
        ])->withBody($body)->post($inbox);
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
