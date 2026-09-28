<?php

namespace App\Http\Resources;

use App\Enums\TransportMode;
use App\Models\TransportTrip;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TripDto',
    description: 'Data Transfer Object for a Transport Trip',
    required: ['id', 'foreignId', 'mode', 'lineName', 'routeLongName', 'tripShortName', 'displayName', 'routeColor', 'routeTextColor'],
    type: 'object'
)]
class TripDto
{
    #[OA\Property(
        property: 'id',
        description: 'Unique identifier for the trip',
        type: 'string',
        format: 'uuid'
    )]
    public string $id;

    #[OA\Property(
        property: 'foreignId',
        description: 'Foreign identifier for the trip',
        type: 'string',
        nullable: true
    )]
    public ?string $foreignId = null;

    #[OA\Property(
        property: 'mode',
        schema: TransportMode::class,
        description: 'Mode of transportation'
    )]
    public TransportMode $mode;

    #[OA\Property(
        property: 'lineName',
        description: 'Name of the line',
        type: 'string',
        nullable: true
    )]
    public ?string $lineName;

    #[OA\Property(
        property: 'routeLongName',
        description: 'Long name of the route',
        type: 'string',
        nullable: true
    )]
    public ?string $routeLongName = null;

    #[OA\Property(
        property: 'tripShortName',
        description: 'Short name of the trip',
        type: 'string',
        nullable: true
    )]
    public ?string $tripShortName = null;

    #[OA\Property(
        property: 'displayName',
        description: 'Display name of the trip',
        type: 'string',
        nullable: true
    )]
    public ?string $displayName = null;

    #[OA\Property(
        property: 'routeColor',
        description: 'Color of the route in HEX format',
        type: 'string',
        nullable: true
    )]
    public ?string $routeColor = null;

    #[OA\Property(
        property: 'routeTextColor',
        description: 'Text color of the route in HEX format',
        type: 'string',
        nullable: true
    )]
    public ?string $routeTextColor = null;

    public function __construct(?TransportTrip $trip = null)
    {
        if ($trip === null) {
            return;
        }

        $this->id = $trip->id;
        $this->foreignId = $trip->foreign_trip_id;
        $this->mode = TransportMode::tryFrom($trip->mode);
        $this->lineName = $trip->line_name;
        $this->routeLongName = $trip->route_long_name;
        $this->tripShortName = $trip->trip_short_name;
        $this->displayName = $trip->display_name;
        $this->routeColor = $trip->route_color;
        $this->routeTextColor = $trip->route_text_color;
    }

    public static function fromRemote(array $data): self
    {
        $dto = new self;
        $dto->id = (string) ($data['id'] ?? '');
        $dto->foreignId = $data['foreignId'] ?? null;
        // $mode is non-nullable; unlike the local constructor, remote data can plausibly
        // be missing/invalid, so fall back to OTHER rather than risk a TypeError.
        $dto->mode = TransportMode::tryFrom($data['mode'] ?? '') ?? TransportMode::OTHER;
        $dto->lineName = $data['lineName'] ?? null;
        $dto->routeLongName = $data['routeLongName'] ?? null;
        $dto->tripShortName = $data['tripShortName'] ?? null;
        $dto->displayName = $data['displayName'] ?? null;
        $dto->routeColor = $data['routeColor'] ?? null;
        $dto->routeTextColor = $data['routeTextColor'] ?? null;

        return $dto;
    }
}
