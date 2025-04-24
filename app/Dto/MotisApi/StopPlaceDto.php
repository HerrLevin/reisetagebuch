<?php

namespace App\Dto\MotisApi;

use Carbon\Carbon;

class StopPlaceDto
{
    public string $name;
    public string $stopId;
    public float $latitude;
    public float $longitude;
    public ?Carbon $arrival;
    public ?Carbon $departure;
    public ?Carbon $scheduledArrival;
    public ?Carbon $scheduledDeparture;

    public function setName(string $name): StopPlaceDto
    {
        $this->name = $name;
        return $this;
    }

    public function setStopId(string $stopId): StopPlaceDto
    {
        $this->stopId = $stopId;
        return $this;
    }

    public function setLatitude(float $latitude): StopPlaceDto
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function setLongitude(float $longitude): StopPlaceDto
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function setArrival(?Carbon $arrival): StopPlaceDto
    {
        $this->arrival = $arrival;
        return $this;
    }

    public function setDeparture(?Carbon $departure): StopPlaceDto
    {
        $this->departure = $departure;
        return $this;
    }

    public function setScheduledArrival(?Carbon $scheduledArrival): StopPlaceDto
    {
        $this->scheduledArrival = $scheduledArrival;
        return $this;
    }

    public function setScheduledDeparture(?Carbon $scheduledDeparture): StopPlaceDto
    {
        $this->scheduledDeparture = $scheduledDeparture;
        return $this;
    }
}
