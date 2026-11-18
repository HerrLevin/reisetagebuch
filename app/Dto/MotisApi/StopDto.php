<?php

namespace App\Dto\MotisApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MotisStopDto',
    description: 'Data Transfer Object for a Stop',
    required: ['stopId', 'tripStopId', 'name', 'latitude', 'longitude', 'distance'],
    type: 'object'
)]
class StopDto
{
    #[OA\Property('stopId', description: 'Unique identifier for the stop', type: 'string')]
    public string $stopId;

    #[OA\Property('tripStopId', description: 'Trip-specific identifier for the stop', type: 'string', nullable: true)]
    public ?string $tripStopId = null;

    #[OA\Property('name', description: 'Name of the stop', type: 'string')]
    public string $name;

    #[OA\Property('latitude', description: 'Latitude of the stop', type: 'number', format: 'float')]
    public float $latitude;

    #[OA\Property('longitude', description: 'Longitude of the stop', type: 'number', format: 'float')]
    public float $longitude;

    #[OA\Property('distance', description: 'Distance to the stop in meters', type: 'integer', nullable: true)]
    public ?int $distance = null;

    public function setStopId(string $stopId): static
    {
        $this->stopId = $stopId;

        return $this;
    }

    public function setTripStopId(?string $tripStopId): static
    {
        $this->tripStopId = $tripStopId;

        return $this;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function setLatitude(float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function setLongitude(float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function setDistance(?int $distance): static
    {
        $this->distance = $distance;

        return $this;
    }
}
