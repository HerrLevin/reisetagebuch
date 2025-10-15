<?php

namespace App\Http\Resources;

use App\Models\LocationPost;
use App\Models\TimestampedUserWaypoint;
use App\Models\TransportPost;

class LocationHistoryEntryDto
{
    public string $id;

    public ?string $name = null;

    public float $latitude;

    public float $longitude;

    public string $type = 'location';

    public string $timestamp;

    /**
     * @return self[]
     */
    public static function fromTransportPost(TransportPost $transportPost): array
    {
        $origin = new self;
        $destination = new self;

        $origin->id = $transportPost->originStop->location->id;
        $origin->name = $transportPost->originStop->location->name;
        $origin->latitude = $transportPost->originStop->location->location->getLatitude();
        $origin->longitude = $transportPost->originStop->location->location->getLongitude();
        $origin->timestamp = ($transportPost->originStop->departure_time ?? $transportPost->originStop->arrival_time)->toIso8601String();

        $destination->id = $transportPost->destinationStop->location->id;
        $destination->name = $transportPost->destinationStop->location->name;
        $destination->latitude = $transportPost->destinationStop->location->location->getLatitude();
        $destination->longitude = $transportPost->destinationStop->location->location->getLongitude();
        $destination->timestamp = ($transportPost->destinationStop->arrival_time ?? $transportPost->destinationStop->departure_time)->toIso8601String();

        return [$origin, $destination];
    }

    public static function fromLocationPost(LocationPost $location): self
    {
        $dto = new self;
        $dto->id = $location->location->id;
        $dto->name = $location->location->name;
        $dto->latitude = $location->location->location->getLatitude();
        $dto->longitude = $location->location->location->getLongitude();
        $dto->timestamp = $location->created_at->toIso8601String();

        return $dto;
    }

    public static function fromWaypoint(TimestampedUserWaypoint $waypoint): ?self
    {
        $dto = new self;
        $dto->id = $waypoint->id;
        $dto->latitude = $waypoint->location->getLatitude();
        $dto->longitude = $waypoint->location->getLongitude();
        $dto->type = 'timestamped_user_waypoint';
        $dto->timestamp = $waypoint->created_at->toIso8601String();

        return $dto;
    }
}
