<?php

namespace App\Services;

use DateTimeZone;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TimeZoneLookupService
{
    private string $apiUrl;

    private VersionService $versionService;

    public function __construct(?VersionService $versionService = null)
    {
        $this->versionService = $versionService ?? new VersionService;
        $this->apiUrl = config('app.timeapi.url');
    }

    /**
     * Resolve the IANA timezone identifier for a coordinate.
     * Never throws — returns null if the lookup fails or is unavailable.
     */
    public function lookup(float $latitude, float $longitude): ?string
    {
        try {
            $response = Http::withUserAgent($this->versionService->getUserAgent())
                ->timeout(5)
                ->get($this->apiUrl, [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);
        } catch (Throwable $e) {
            Log::warning('Timezone lookup failed', ['error' => $e->getMessage()]);

            return null;
        }

        if (! $response->ok()) {
            Log::warning('Unknown response (timezone lookup)', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $timezone = $response->json('timeZone');

        if (! is_string($timezone) || ! in_array($timezone, DateTimeZone::listIdentifiers(), true)) {
            return null;
        }

        return $timezone;
    }
}
