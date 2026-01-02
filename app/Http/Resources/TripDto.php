<?php

namespace App\Http\Resources;

use App\Models\TransportTrip;

class TripDto
{
    public string $id;

    public ?string $foreignId = null;

    public string $mode;

    public ?string $lineName;

    public ?string $routeLongName = null;

    public ?string $tripShortName = null;

    public ?string $displayName = null;

    public ?string $routeColor = null;

    public ?string $routeTextColor = null;

    public function __construct(TransportTrip $trip)
    {
        $this->id = $trip->id;
        $this->foreignId = $trip->foreign_trip_id;
        $this->mode = $trip->mode;
        $this->lineName = $trip->line_name;
        $this->routeLongName = $trip->route_long_name;
        $this->tripShortName = $trip->trip_short_name;
        $this->displayName = $trip->display_name;
        $this->routeColor = $trip->route_color;
        $this->routeTextColor = $trip->route_text_color;
    }
}
