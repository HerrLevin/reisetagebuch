<?php

declare(strict_types=1);

namespace App\Dto\ActivityPub\Extensions;

use App\Dto\ActivityPub\Extensions\Concerns\ParsesUntrustedFields;
use App\Http\Resources\StopDto;

final class RtbStopData
{
    use ParsesUntrustedFields;

    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly RtbLocationData $location,
        public readonly ?string $arrivalTime,
        public readonly ?string $departureTime,
        public readonly ?int $arrivalDelay,
        public readonly ?int $departureDelay,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location->toArray(),
            'arrivalTime' => $this->arrivalTime,
            'departureTime' => $this->departureTime,
            'arrivalDelay' => $this->arrivalDelay,
            'departureDelay' => $this->departureDelay,
        ];
    }

    public static function fromDto(StopDto $stop): self
    {
        return new self(
            id: $stop->id,
            name: $stop->name,
            location: RtbLocationData::fromDto($stop->location),
            arrivalTime: $stop->arrivalTime,
            departureTime: $stop->departureTime,
            arrivalDelay: $stop->arrivalDelay,
            departureDelay: $stop->departureDelay,
        );
    }

    /**
     * Returns null (rejecting the whole stop) when its location is missing/invalid.
     */
    public static function fromArray(mixed $data): ?self
    {
        if (! is_array($data)) {
            return null;
        }

        $location = RtbLocationData::fromArray($data['location'] ?? null);
        if ($location === null) {
            return null;
        }

        return new self(
            id: self::parseString($data['id'] ?? null) ?? '',
            name: self::parseString($data['name'] ?? null) ?? '',
            location: $location,
            arrivalTime: self::parseString($data['arrivalTime'] ?? null),
            departureTime: self::parseString($data['departureTime'] ?? null),
            arrivalDelay: self::parseInt($data['arrivalDelay'] ?? null),
            departureDelay: self::parseInt($data['departureDelay'] ?? null),
        );
    }
}
