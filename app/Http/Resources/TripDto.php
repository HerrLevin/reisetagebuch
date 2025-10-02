<?php

namespace App\Http\Resources;

use App\Models\TransportTrip;

class TripDto
{
    public string $id;

    public string $mode;

    public string $lineName;

    public ?string $routeLongName = null;

    public ?string $tripShortName = null;

    public ?string $displayName = null;

    public function __construct(TransportTrip $trip)
    {
        $this->id = $trip->id;
        $this->mode = $trip->mode;
        $this->lineName = $trip->line_name;
        $this->routeLongName = $trip->route_long_name;
        $this->tripShortName = $trip->trip_short_name;
        $this->displayName = $trip->display_name;
    }
}
