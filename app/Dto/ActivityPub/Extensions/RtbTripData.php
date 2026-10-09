<?php

declare(strict_types=1);

namespace App\Dto\ActivityPub\Extensions;

use App\Dto\ActivityPub\Extensions\Concerns\ParsesUntrustedFields;
use App\Enums\TransportMode;
use App\Http\Resources\TripDto;

final class RtbTripData
{
    use ParsesUntrustedFields;

    public function __construct(
        public readonly string $id,
        public readonly ?string $foreignId,
        public readonly ?string $foreignIdSourceUrl,
        public readonly ?string $foreignIdSourceFormat,
        public readonly TransportMode $mode,
        public readonly ?string $lineName,
        public readonly ?string $routeLongName,
        public readonly ?string $tripShortName,
        public readonly ?string $displayName,
        public readonly ?string $routeColor,
        public readonly ?string $routeTextColor,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'foreignId' => $this->foreignId,
            'foreignIdSourceUrl' => $this->foreignIdSourceUrl,
            'foreignIdSourceFormat' => $this->foreignIdSourceFormat,
            'mode' => $this->mode->value,
            'lineName' => $this->lineName,
            'routeLongName' => $this->routeLongName,
            'tripShortName' => $this->tripShortName,
            'displayName' => $this->displayName,
            'routeColor' => $this->routeColor,
            'routeTextColor' => $this->routeTextColor,
        ];
    }

    public static function fromDto(TripDto $trip): self
    {
        $hasForeignId = $trip->foreignId !== null;

        return new self(
            id: $trip->id,
            foreignId: $trip->foreignId,
            foreignIdSourceUrl: $hasForeignId ? config('app.motis.api_url') : null,
            foreignIdSourceFormat: $hasForeignId ? 'motis' : null,
            mode: $trip->mode,
            lineName: $trip->lineName,
            routeLongName: $trip->routeLongName,
            tripShortName: $trip->tripShortName,
            displayName: $trip->displayName,
            routeColor: $trip->routeColor,
            routeTextColor: $trip->routeTextColor,
        );
    }

    public static function fromArray(mixed $data): ?self
    {
        if (! is_array($data)) {
            return null;
        }

        return new self(
            id: self::parseString($data['id'] ?? null) ?? '',
            foreignId: self::parseString($data['foreignId'] ?? null),
            foreignIdSourceUrl: self::parseString($data['foreignIdSourceUrl'] ?? null),
            foreignIdSourceFormat: self::parseString($data['foreignIdSourceFormat'] ?? null),
            // Unlike the local TripDto constructor, remote data can plausibly be
            // missing/invalid, so fall back to OTHER rather than risk a TypeError on
            // this non-nullable field.
            mode: self::parseEnum($data['mode'] ?? null, TransportMode::class) ?? TransportMode::OTHER,
            lineName: self::parseString($data['lineName'] ?? null),
            routeLongName: self::parseString($data['routeLongName'] ?? null),
            tripShortName: self::parseString($data['tripShortName'] ?? null),
            displayName: self::parseString($data['displayName'] ?? null),
            routeColor: self::parseString($data['routeColor'] ?? null),
            routeTextColor: self::parseString($data['routeTextColor'] ?? null),
        );
    }
}
