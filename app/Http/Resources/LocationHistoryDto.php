<?php

namespace App\Http\Resources;

use App\Models\LocationPost;
use App\Models\TimestampedUserWaypoint;

class LocationHistoryDto
{
    public string $id;

    public ?string $name = null;

    public float $latitude;

    public float $longitude;

    public string $type = 'location';

    public string $timestamp;

    public static function fromLocationPost(LocationPost $location)
    {
        $dto = new self;
        $dto->id = $location->location->id;
        $dto->name = $location->location->name;
        $dto->latitude = $location->location->location->getLatitude();
        $dto->longitude = $location->location->location->getLongitude();
        $dto->timestamp = $location->created_at->getTimestamp();

        return $dto;
    }

    public static function fromWaypoint(TimestampedUserWaypoint $waypoint): ?self
    {
        $dto = new self;
        $dto->id = $waypoint->id;
        $dto->latitude = $waypoint->location->getLatitude();
        $dto->longitude = $waypoint->location->getLongitude();
        $dto->type = 'timestamped_user_waypoint';
        $dto->timestamp = $waypoint->created_at->getTimestamp();

        return $dto;
    }
}
