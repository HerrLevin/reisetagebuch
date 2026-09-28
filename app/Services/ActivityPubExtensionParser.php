<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PostMetaInfo\TravelReason;
use App\Enums\TransportMode;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Defensively parses the optional `rtbExtension` reisetagebuch-to-reisetagebuch envelope
 * on an incoming ActivityPub Note. The input is untrusted JSON from an arbitrary remote
 * server, so every field is type-checked and size-capped, and any single malformed piece
 * degrades to a smaller/empty result rather than rejecting the whole envelope — the only
 * thing that must never happen is an exception escaping into ordinary Note ingestion.
 */
class ActivityPubExtensionParser
{
    private const int SUPPORTED_VERSION = 1;

    private const int MAX_STRING_LENGTH = 500;

    private const int MAX_ARRAY_ITEMS = 50;

    private const int MAX_GEOMETRY_BYTES = 200_000;

    /**
     * @return array{rtbVersion: int, postType: string, location: array|null, transport: array|null}|null
     */
    public function parse(array $object): ?array
    {
        try {
            return $this->parseExtension($object);
        } catch (Throwable $e) {
            Log::warning('Failed to parse RTB extension, ignoring it', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * @return array{rtbVersion: int, postType: string, location: array|null, transport: array|null}|null
     */
    private function parseExtension(array $object): ?array
    {
        $extension = $object['rtbExtension'] ?? null;
        if (! is_array($extension)) {
            return null;
        }

        if (($extension['rtbVersion'] ?? null) !== self::SUPPORTED_VERSION) {
            return null;
        }

        $postType = $extension['postType'] ?? null;
        if (! in_array($postType, ['location', 'transport'], true)) {
            return null;
        }

        $payload = match ($postType) {
            'location' => $this->parseLocation($extension['location'] ?? null),
            'transport' => $this->parseTransport($extension['transport'] ?? null),
        };

        if ($payload === null) {
            return null;
        }

        return [
            'rtbVersion' => self::SUPPORTED_VERSION,
            'postType' => $postType,
            $postType => $payload,
        ];
    }

    private function parseLocation(mixed $data): ?array
    {
        if (! is_array($data)) {
            return null;
        }

        $core = $this->parseLocationCore($data);
        if ($core === null) {
            return null;
        }

        return [
            ...$core,
            'travelReason' => $this->parseEnumValue($data['travelReason'] ?? null, TravelReason::class),
            'visitedAt' => $this->parseString($data['visitedAt'] ?? null),
        ];
    }

    private function parseTransport(mixed $data): ?array
    {
        if (! is_array($data)) {
            return null;
        }

        $originStop = $this->parseStop($data['originStop'] ?? null);
        $destinationStop = $this->parseStop($data['destinationStop'] ?? null);
        $trip = $this->parseTrip($data['trip'] ?? null);

        if ($originStop === null || $destinationStop === null || $trip === null) {
            return null;
        }

        return [
            'originStop' => $originStop,
            'destinationStop' => $destinationStop,
            'trip' => $trip,
            'manualDepartureTime' => $this->parseString($data['manualDepartureTime'] ?? null),
            'manualArrivalTime' => $this->parseString($data['manualArrivalTime'] ?? null),
            'travelReason' => $this->parseEnumValue($data['travelReason'] ?? null, TravelReason::class),
            'distance' => is_numeric($data['distance'] ?? null) ? (int) $data['distance'] : null,
            'duration' => is_numeric($data['duration'] ?? null) ? (int) $data['duration'] : null,
            'userGeometry' => $this->parseGeometry($data['userGeometry'] ?? null),
        ];
    }

    private function parseStop(mixed $data): ?array
    {
        if (! is_array($data)) {
            return null;
        }

        $location = $this->parseLocationCore($data['location'] ?? null);
        if ($location === null) {
            return null;
        }

        return [
            'id' => $this->parseString($data['id'] ?? null) ?? '',
            'name' => $this->parseString($data['name'] ?? null) ?? '',
            'location' => $location,
            'arrivalTime' => $this->parseString($data['arrivalTime'] ?? null),
            'departureTime' => $this->parseString($data['departureTime'] ?? null),
            'arrivalDelay' => is_numeric($data['arrivalDelay'] ?? null) ? (int) $data['arrivalDelay'] : null,
            'departureDelay' => is_numeric($data['departureDelay'] ?? null) ? (int) $data['departureDelay'] : null,
        ];
    }

    private function parseTrip(mixed $data): ?array
    {
        if (! is_array($data)) {
            return null;
        }

        return [
            'id' => $this->parseString($data['id'] ?? null) ?? '',
            'foreignId' => $this->parseString($data['foreignId'] ?? null),
            'mode' => $this->parseEnumValue($data['mode'] ?? null, TransportMode::class, TransportMode::OTHER->value),
            'lineName' => $this->parseString($data['lineName'] ?? null),
            'routeLongName' => $this->parseString($data['routeLongName'] ?? null),
            'tripShortName' => $this->parseString($data['tripShortName'] ?? null),
            'displayName' => $this->parseString($data['displayName'] ?? null),
            'routeColor' => $this->parseString($data['routeColor'] ?? null),
            'routeTextColor' => $this->parseString($data['routeTextColor'] ?? null),
        ];
    }

    /**
     * Fields shared between a location post's own location and a transport stop's
     * location. Returns null (rejecting just this location block) when the coordinates
     * are missing or out of range — everything else degrades field-by-field instead.
     */
    private function parseLocationCore(mixed $data): ?array
    {
        if (! is_array($data)) {
            return null;
        }

        $latitude = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;
        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            return null;
        }

        $latitude = (float) $latitude;
        $longitude = (float) $longitude;
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return null;
        }

        return [
            'id' => $this->parseString($data['id'] ?? null) ?? '',
            'name' => $this->parseString($data['name'] ?? null) ?? '',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'timezone' => $this->parseString($data['timezone'] ?? null),
            'emoji' => $this->parseString($data['emoji'] ?? null) ?? '❔',
            'tags' => $this->parseTags($data['tags'] ?? null),
            'identifiers' => $this->parseIdentifiers($data['identifiers'] ?? null),
        ];
    }

    private function parseTags(mixed $data): array
    {
        if (! is_array($data)) {
            return [];
        }

        $tags = [];
        foreach (array_slice($data, 0, self::MAX_ARRAY_ITEMS) as $tag) {
            if (! is_array($tag)) {
                continue;
            }
            $key = $this->parseString($tag['key'] ?? null);
            $value = $this->parseString($tag['value'] ?? null);
            if ($key === null || $value === null) {
                continue;
            }
            $tags[] = ['key' => $key, 'value' => $value];
        }

        return $tags;
    }

    private function parseIdentifiers(mixed $data): array
    {
        if (! is_array($data)) {
            return [];
        }

        $identifiers = [];
        foreach (array_slice($data, 0, self::MAX_ARRAY_ITEMS) as $identifier) {
            if (! is_array($identifier)) {
                continue;
            }
            $type = $this->parseString($identifier['type'] ?? null);
            $origin = $this->parseString($identifier['origin'] ?? null);
            $value = $this->parseString($identifier['identifier'] ?? null);
            if ($type === null || $origin === null || $value === null) {
                continue;
            }
            $identifiers[] = ['type' => $type, 'origin' => $origin, 'identifier' => $value];
        }

        return $identifiers;
    }

    private function parseGeometry(mixed $data): ?array
    {
        if (! is_array($data)) {
            return null;
        }

        if (strlen(json_encode($data) ?: '') > self::MAX_GEOMETRY_BYTES) {
            return null;
        }

        return $data;
    }

    private function parseString(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        return mb_substr($value, 0, self::MAX_STRING_LENGTH);
    }

    /**
     * @param  class-string<TravelReason|TransportMode>  $enumClass
     */
    private function parseEnumValue(mixed $value, string $enumClass, mixed $fallbackValue = null): ?string
    {
        if (! is_string($value)) {
            return $fallbackValue;
        }

        return $enumClass::tryFrom($value)?->value;
    }
}
