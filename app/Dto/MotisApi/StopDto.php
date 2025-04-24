<?php

namespace App\Dto\MotisApi;

class StopDto
{
    public readonly string $stopId;
    public readonly string $name;
    public readonly float $latitude;
    public readonly float $longitude;
    public ?int $distance = null;
    public function __construct(
        string $stopId,
        string $name,
        float $latitude,
        float $longitude,
        ?int $distance = null
    ) {
        $this->stopId    = $stopId;
        $this->name      = $name;
        $this->latitude  = $latitude;
        $this->longitude = $longitude;
        $this->distance  = $distance;
    }
}
