<?php

declare(strict_types=1);

namespace App\Dto\ActivityPub\Extensions;

use App\Dto\ActivityPub\Extensions\Concerns\ParsesUntrustedFields;
use App\Enums\PostMetaInfo\TravelReason;
use App\Http\Resources\PostTypes\TransportPost;

final class RtbTransportExtension implements RtbExtension
{
    use ParsesUntrustedFields;

    public const string POST_TYPE = 'transport';

    public function __construct(
        public readonly int $rtbVersion,
        public readonly RtbStopData $originStop,
        public readonly RtbStopData $destinationStop,
        public readonly RtbTripData $trip,
        public readonly ?string $manualDepartureTime,
        public readonly ?string $manualArrivalTime,
        public readonly ?TravelReason $travelReason,
        public readonly int $distance,
        public readonly int $duration,
        public readonly ?array $userGeometry,
    ) {}

    public function toArray(): array
    {
        return [
            'rtbVersion' => $this->rtbVersion,
            'postType' => self::POST_TYPE,
            'transport' => [
                'originStop' => $this->originStop->toArray(),
                'destinationStop' => $this->destinationStop->toArray(),
                'trip' => $this->trip->toArray(),
                'manualDepartureTime' => $this->manualDepartureTime,
                'manualArrivalTime' => $this->manualArrivalTime,
                'travelReason' => $this->travelReason?->value,
                'distance' => $this->distance,
                'duration' => $this->duration,
                'userGeometry' => $this->userGeometry,
            ],
        ];
    }

    public static function fromPost(TransportPost $post): self
    {
        return new self(
            rtbVersion: RtbExtensionFactory::CURRENT_VERSION,
            originStop: RtbStopData::fromDto($post->originStop),
            destinationStop: RtbStopData::fromDto($post->destinationStop),
            trip: RtbTripData::fromDto($post->trip),
            manualDepartureTime: $post->manualDepartureTime,
            manualArrivalTime: $post->manualArrivalTime,
            travelReason: $post->travelReason,
            distance: $post->distance,
            duration: $post->duration,
            userGeometry: $post->userGeometry,
        );
    }

    /**
     * @param  array<string, mixed>  $data  the whole rtbExtension envelope
     */
    public static function fromArray(array $data): ?self
    {
        $transportRaw = $data['transport'] ?? null;
        if (! is_array($transportRaw)) {
            return null;
        }

        $originStop = RtbStopData::fromArray($transportRaw['originStop'] ?? null);
        $destinationStop = RtbStopData::fromArray($transportRaw['destinationStop'] ?? null);
        $trip = RtbTripData::fromArray($transportRaw['trip'] ?? null);
        if ($originStop === null || $destinationStop === null || $trip === null) {
            return null;
        }

        return new self(
            rtbVersion: RtbExtensionFactory::CURRENT_VERSION,
            originStop: $originStop,
            destinationStop: $destinationStop,
            trip: $trip,
            manualDepartureTime: self::parseString($transportRaw['manualDepartureTime'] ?? null),
            manualArrivalTime: self::parseString($transportRaw['manualArrivalTime'] ?? null),
            travelReason: self::parseEnum($transportRaw['travelReason'] ?? null, TravelReason::class),
            distance: self::parseInt($transportRaw['distance'] ?? null) ?? 0,
            duration: self::parseInt($transportRaw['duration'] ?? null) ?? 0,
            userGeometry: self::parseGeometry($transportRaw['userGeometry'] ?? null),
        );
    }
}
