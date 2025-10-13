<?php

namespace App\Http\Resources;

use App\Models\TransportPost;
use Clickbar\Magellan\Data\Geometries\LineString;

class TripHistoryEntryDto
{
    public string $id;

    public ?LineString $geometry;

    public TripDto $trip;

    public function __construct(TransportPost $transportPost, ?LineString $geometry)
    {
        $this->id = $transportPost->id;
        $this->trip = new TripDto($transportPost->transportTrip);
        $this->geometry = $geometry;
    }
}
