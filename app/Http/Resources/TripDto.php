<?php

namespace App\Http\Resources;

use App\Models\TransportTrip;

class TripDto
{
    public string $id;

    public string $mode;

    public string $lineName;

    public function __construct(TransportTrip $trip)
    {
        $this->id = $trip->id;
        $this->mode = $trip->mode;
        $this->lineName = $trip->line_name;
    }
}
