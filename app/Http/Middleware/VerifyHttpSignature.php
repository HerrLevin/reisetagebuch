<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyHttpSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $signatureHeader = $request->header('Signature');
        if (! $signatureHeader) {
            Log::warning('Inbox request missing Signature header');

            return response()->json(['error' => 'Missing Signature header'], 401);
        }

        $params = $this->parseSignatureHeader($signatureHeader);
        if (! $params || ! isset($params['keyId'], $params['signature'], $params['headers'])) {
            Log::warning('Invalid Signature header format');

            return response()->json(['error' => 'Invalid Signature header'], 401);
        }

        // Verify Digest header matches body
        $digestHeader = $request->header('Digest');
        if ($digestHeader) {
            $expectedDigest = 'SHA-256='.base64_encode(hash('sha256', $request->getContent(), true));
            if (! hash_equals($expectedDigest, $digestHeader)) {
                Log::warning('Digest mismatch', ['expected' => $expectedDigest, 'actual' => $digestHeader]);

                return response()->json(['error' => 'Digest mismatch'], 401);
            }
        }

        // Fetch the public key
        $publicKeyPem = $this->fetchPublicKey($params['keyId']);
        if (! $publicKeyPem) {
            Log::warning('Could not fetch public key', ['keyId' => $params['keyId']]);

            return response()->json(['error' => 'Could not fetch public key'], 401);
        }

        // Build the signing string
        $signedHeaders = explode(' ', $params['headers']);
        $signingStringParts = [];
        foreach ($signedHeaders as $header) {
            if ($header === '(request-target)') {
                $signingStringParts[] = '(request-target): '.strtolower($request->method()).' '.$request->getRequestUri();
            } elseif ($header === 'host') {
                $signingStringParts[] = 'host: '.$request->header('host');
            } elseif ($header === 'date') {
                $signingStringParts[] = 'date: '.$request->header('date');
            } elseif ($header === 'digest') {
                $signingStringParts[] = 'digest: '.$request->header('digest');
            } elseif ($header === 'content-type') {
                $signingStringParts[] = 'content-type: '.$request->header('content-type');
            } else {
                $signingStringParts[] = $header.': '.$request->header($header);
            }
        }
        $signingString = implode("\n", $signingStringParts);

        // Verify signature
        $decodedSignature = base64_decode($params['signature']);
        $publicKey = openssl_pkey_get_public($publicKeyPem);
        if (! $publicKey) {
            Log::warning('Invalid public key PEM');

            return response()->json(['error' => 'Invalid public key'], 401);
        }

        $algorithm = $params['algorithm'] ?? 'rsa-sha256';
        $opensslAlgo = match ($algorithm) {
            'rsa-sha256' => OPENSSL_ALGO_SHA256,
            'rsa-sha512' => OPENSSL_ALGO_SHA512,
            default => OPENSSL_ALGO_SHA256,
        };

        $verified = openssl_verify($signingString, $decodedSignature, $publicKey, $opensslAlgo);
        if ($verified !== 1) {
            Log::warning('HTTP Signature verification failed', ['keyId' => $params['keyId']]);

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        return $next($request);
    }

    private function parseSignatureHeader(string $header): ?array
    {
        $params = [];
        // Match key="value" pairs
        if (preg_match_all('/(\w+)="([^"]*)"/', $header, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $params[$match[1]] = $match[2];
            }
        }

        return ! empty($params) ? $params : null;
    }

    private function fetchPublicKey(string $keyId): ?string
    {
        // Strip fragment to get actor URL
        $actorUrl = strtok($keyId, '#');

        return Cache::remember('ap_public_key:'.md5($keyId), 3600, function () use ($actorUrl, $keyId) {
            try {
                $response = Http::withHeaders([
                    'Accept' => 'application/activity+json',
                ])->timeout(10)->get($actorUrl);

                if ($response->successful()) {
                    $actor = $response->json();
                    $publicKey = $actor['publicKey'] ?? null;
                    if ($publicKey && ($publicKey['id'] === $keyId) && isset($publicKey['publicKeyPem'])) {
                        return $publicKey['publicKeyPem'];
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error fetching public key: '.$e->getMessage());
            }

            return null;
        });
    }
}
