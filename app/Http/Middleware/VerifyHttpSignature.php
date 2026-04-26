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
            Log::warning('VerifyHttpSignature: missing Signature header', [
                'uri' => $request->getRequestUri(),
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Missing Signature header'], 401);
        }

        $params = $this->parseSignatureHeader($signatureHeader);
        if (! $params || ! isset($params['keyId'], $params['signature'], $params['headers'])) {
            Log::warning('VerifyHttpSignature: invalid Signature header format', [
                'uri' => $request->getRequestUri(),
                'signature_header' => $signatureHeader,
            ]);

            return response()->json(['error' => 'Invalid Signature header'], 401);
        }

        // Verify Digest header matches body
        $digestHeader = $request->header('Digest');
        if ($digestHeader) {
            $expectedDigest = 'SHA-256='.base64_encode(hash('sha256', $request->getContent(), true));
            if (! hash_equals($expectedDigest, $digestHeader)) {
                Log::warning('VerifyHttpSignature: digest mismatch', [
                    'keyId' => $params['keyId'],
                    'expected' => $expectedDigest,
                    'actual' => $digestHeader,
                ]);

                return response()->json(['error' => 'Digest mismatch'], 401);
            }
        }

        // Fetch the public key
        $publicKeyPem = $this->fetchPublicKey($params['keyId']);
        if (! $publicKeyPem) {
            Log::warning('VerifyHttpSignature: could not fetch public key', ['keyId' => $params['keyId']]);

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
            Log::warning('VerifyHttpSignature: invalid public key PEM', ['keyId' => $params['keyId']]);

            return response()->json(['error' => 'Invalid public key'], 401);
        }

        $algorithm = $params['algorithm'] ?? 'rsa-sha256';
        $opensslAlgo = match ($algorithm) {
            'rsa-sha256', 'hs2019' => OPENSSL_ALGO_SHA256,
            'rsa-sha512' => OPENSSL_ALGO_SHA512,
            default => OPENSSL_ALGO_SHA256,
        };

        $verified = openssl_verify($signingString, $decodedSignature, $publicKey, $opensslAlgo);
        if ($verified !== 1) {
            Log::warning('VerifyHttpSignature: signature verification failed', [
                'keyId' => $params['keyId'],
                'algorithm' => $algorithm,
                'signed_headers' => $params['headers'],
                'signing_string' => $signingString,
            ]);

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
        $cacheKey = 'ap_public_key:'.md5($keyId);

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/activity+json',
            ])->timeout(10)->get($actorUrl);

            if ($response->successful()) {
                $actor = $response->json();
                $publicKey = $actor['publicKey'] ?? null;
                if ($publicKey && ($publicKey['id'] === $keyId) && isset($publicKey['publicKeyPem'])) {
                    Cache::put($cacheKey, $publicKey['publicKeyPem'], 3600);

                    return $publicKey['publicKeyPem'];
                }

                Log::warning('VerifyHttpSignature: public key not found or keyId mismatch in actor response', [
                    'keyId' => $keyId,
                    'actor_url' => $actorUrl,
                    'has_public_key' => isset($actor['publicKey']),
                    'actor_key_id' => $actor['publicKey']['id'] ?? null,
                ]);
            } else {
                Log::warning('VerifyHttpSignature: failed to fetch actor', [
                    'actor_url' => $actorUrl,
                    'status' => $response->status(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('VerifyHttpSignature: error fetching public key', [
                'actor_url' => $actorUrl,
                'error' => $e->getMessage(),
            ]);
        }

        // Don't cache failures — allow retry on next request
        return null;
    }
}
