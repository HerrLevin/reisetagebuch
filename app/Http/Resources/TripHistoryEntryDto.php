<?php

namespace App\Http\Resources;

use App\Models\TransportPost;
use Clickbar\Magellan\Data\Geometries\LineString;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TripHistoryEntryDto',
    description: 'Trip History Entry Data Transfer Object',
    required: ['id', 'geometry', 'trip'],
    type: 'object'
)]
class TripHistoryEntryDto
{
    #[OA\Property('id', description: 'Transport Post ID', type: 'string', format: 'uuid')]
    public string $id;

    #[OA\Property(
        property: 'geometry',
        description: 'Geometry of the trip as a GeoJSON LineString',
        type: 'object',
        nullable: true,
    )]
    public ?LineString $geometry;

    #[OA\Property(
        property: 'trip',
        ref: TripDto::class,
        description: 'Trip Data Transfer Object'
    )]
    public TripDto $trip;

    public function __construct(TransportPost $transportPost, ?LineString $geometry)
    {
        $this->id = $transportPost->id;
        $this->trip = new TripDto($transportPost->transportTrip);
        $this->geometry = $geometry;
    }
}
