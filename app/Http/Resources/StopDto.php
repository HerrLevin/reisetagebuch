<?php

namespace App\Http\Resources;

use App\Models\TransportTripStop;

class StopDto
{
    public string $id;

    public string $name;

    public LocationDto $location;

    public ?string $arrivalTime;

    public ?string $departureTime;

    public ?int $arrivalDelay = null;

    public ?int $departureDelay = null;

    public function __construct(TransportTripStop $stop)
    {
        $this->id = $stop->id;
        $this->name = $stop->location->name;
        $this->location = new LocationDto($stop->location);
        $this->arrivalTime = $stop->arrival_time?->toIso8601String();
        $this->departureTime = $stop->departure_time?->toIso8601String();
        $this->arrivalDelay = $stop->arrival_delay;
        $this->departureDelay = $stop->departure_delay;
    }
}
