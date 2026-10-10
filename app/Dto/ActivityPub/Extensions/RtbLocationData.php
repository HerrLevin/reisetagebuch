<?php

declare(strict_types=1);

namespace App\Dto\ActivityPub\Extensions;

use App\Dto\ActivityPub\Extensions\Concerns\ParsesUntrustedFields;
use App\Http\Resources\LocationDto;

final class RtbLocationData
{
    use ParsesUntrustedFields;

    /**
     * @param  RtbTagData[]  $tags
     * @param  RtbIdentifierData[]  $identifiers
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly ?string $timezone,
        public readonly string $emoji,
        public readonly array $tags,
        public readonly array $identifiers,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'timezone' => $this->timezone,
            'emoji' => $this->emoji,
            'tags' => array_map(fn (RtbTagData $tag) => $tag->toArray(), $this->tags),
            'identifiers' => array_map(fn (RtbIdentifierData $identifier) => $identifier->toArray(), $this->identifiers),
        ];
    }

    /**
     * distance is deliberately not carried over: LocationDto::$distance is a
     * viewer-relative "distance to a nearby-search origin" value, meaningless once
     * federated to another instance.
     */
    public static function fromDto(LocationDto $location): self
    {
        return new self(
            id: $location->id,
            name: $location->name,
            latitude: $location->latitude,
            longitude: $location->longitude,
            timezone: $location->timezone,
            emoji: $location->emoji,
            tags: array_map(fn ($tag) => RtbTagData::fromDto($tag), $location->tags),
            identifiers: array_map(fn ($identifier) => RtbIdentifierData::fromDto($identifier), $location->identifiers),
        );
    }

    /**
     * Returns null (rejecting the whole location) only when the coordinates are
     * missing/out of range — everything else degrades field-by-field instead.
     */
    public static function fromArray(mixed $data): ?self
    {
        if (! is_array($data)) {
            return null;
        }

        $latitude = self::parseFloat($data['latitude'] ?? null);
        $longitude = self::parseFloat($data['longitude'] ?? null);
        if ($latitude === null || $longitude === null) {
            return null;
        }
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return null;
        }

        return new self(
            id: self::parseString($data['id'] ?? null) ?? '',
            name: self::parseString($data['name'] ?? null) ?? '',
            latitude: $latitude,
            longitude: $longitude,
            timezone: self::parseString($data['timezone'] ?? null),
            emoji: self::parseString($data['emoji'] ?? null) ?? '❔',
            tags: self::parseBoundedList($data['tags'] ?? null, fn ($raw) => RtbTagData::fromArray($raw)),
            identifiers: self::parseBoundedList($data['identifiers'] ?? null, fn ($raw) => RtbIdentifierData::fromArray($raw)),
        );
    }
}
