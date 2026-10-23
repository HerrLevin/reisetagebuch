<?php

namespace App\Http\Resources;

use App\Dto\ActivityPub\Extensions\RtbTripData;
use App\Enums\TransportMode;
use App\Models\TransportTrip;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TripDto',
    description: 'Data Transfer Object for a Transport Trip',
    required: ['id', 'foreignId', 'mode', 'lineName', 'routeLongName', 'tripShortName', 'displayName', 'routeColor', 'routeTextColor', 'licenseName', 'licenseHumanName', 'licenseUrl', 'licenseAttribution'],
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

    #[OA\Property(
        property: 'licenseName',
        description: 'Name of the license covering this trip\'s source data, if known',
        type: 'string',
        nullable: true
    )]
    public ?string $licenseName = null;

    #[OA\Property(
        property: 'licenseHumanName',
        description: 'Name of the organization that issued this trip\'s source data, if known',
        type: 'string',
        nullable: true
    )]
    public ?string $licenseHumanName = null;

    #[OA\Property(
        property: 'licenseUrl',
        description: 'URL with more information about the license, if known',
        type: 'string',
        nullable: true
    )]
    public ?string $licenseUrl = null;

    #[OA\Property(
        property: 'licenseAttribution',
        description: 'Attribution text to display for this trip\'s source data, if required',
        type: 'string',
        nullable: true
    )]
    public ?string $licenseAttribution = null;

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

        if ($trip->license) {
            $this->licenseName = $trip->license->getDisplayName();
            $this->licenseHumanName = $trip->license->human_name;
            $this->licenseUrl = $trip->license->getDisplayUrl();
            $this->licenseAttribution = $trip->license->getAttributionText();
        }
    }

    public static function fromRtb(RtbTripData $data): self
    {
        $dto = new self;
        $dto->id = $data->id;
        $dto->foreignId = $data->foreignId;
        $dto->mode = $data->mode;
        $dto->lineName = $data->lineName;
        $dto->routeLongName = $data->routeLongName;
        $dto->tripShortName = $data->tripShortName;
        $dto->displayName = $data->displayName;
        $dto->routeColor = $data->routeColor;
        $dto->routeTextColor = $data->routeTextColor;
        $dto->licenseName = $data->licenseName;
        $dto->licenseHumanName = $data->licenseHumanName;
        $dto->licenseUrl = $data->licenseUrl;
        $dto->licenseAttribution = $data->licenseAttribution;

        return $dto;
    }
}
