<?php

namespace App\Services;

use App\Http\Resources\UserDto;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ActivityPubService
{
    public function deliverActivity(UserDto $user, string $followerActorId, array $activity): void
    {
        Log::info('Deliver Activity for actor: '.$followerActorId);
        // Fetch the follower's actor to get their inbox
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/activity+json',
            ])->get($followerActorId);
            if ($response->successful()) {
                $followerActor = $response->json();
                $inbox = $followerActor['inbox'] ?? null;
                if (! $inbox) {
                    Log::warning('No inbox found for actor: '.$followerActorId, ['followerActor' => $followerActor, 'response' => $response->body()]);

                    return; // No inbox, can't send
                }
            } else {
                Log::warning('No inbox found for actor: '.$followerActorId, ['response' => $response->body()]);

                return; // Can't fetch actor
            }
        } catch (\Exception $e) {
            Log::error('Error fetching actor: '.$followerActorId.' Error: '.$e->getMessage());

            return; // Error fetching
        }

        // Prepare the request
        $body = json_encode($activity);
        $date = now()->toRfc7231String();
        $digest = 'SHA-256='.base64_encode(hash('sha256', $body, true));
        $host = parse_url($inbox, PHP_URL_HOST);

        // Create signature
        $signature = $this->createSignature($user, 'POST', parse_url($inbox, PHP_URL_PATH), $host, $date, $digest);

        // Send to inbox
        try {
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
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            // Log error or handle
        }
    }

    private function createSignature(UserDto $user, string $method, string $path, string $host, string $date, string $digest): string
    {
        $keyId = route('ap.actor-key', ['username' => $user->username]);
        $headers = '(request-target) host date digest';
        $stringToSign = '(request-target): '.strtolower($method)." {$path}\nhost: {$host}\ndate: {$date}\ndigest: {$digest}";

        $userModel = User::whereId($user->id)->first();

        $signature = '';
        openssl_sign($stringToSign, $signature, $userModel->private_key, OPENSSL_ALGO_SHA256);

        return 'keyId="'.$keyId.'",algorithm="rsa-sha256",headers="'.$headers.'",signature="'.base64_encode($signature).'"';
    }
}
