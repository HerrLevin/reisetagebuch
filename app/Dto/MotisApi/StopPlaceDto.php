<?php

namespace App\Dto\MotisApi;

use Carbon\Carbon;

class StopPlaceDto extends StopDto
{
    public ?Carbon $arrival;
    public ?Carbon $departure;
    public ?Carbon $scheduledArrival;
    public ?Carbon $scheduledDeparture;

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
