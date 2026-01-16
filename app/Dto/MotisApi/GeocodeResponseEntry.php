<?php

namespace App\Dto\MotisApi;

use App\Http\Resources\LocationIdentifierDto;
use App\Models\Location;
use App\Traits\JsonResponseObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MotisGeocodeResponseEntry',
    required: ['type', 'tokens', 'name', 'identifier', 'lat', 'lon', 'score', 'id', 'level', 'street', 'houseNumber', 'zip', 'areas', 'identifiers'],
    type: 'object',
)]
class GeocodeResponseEntry
{
    use JsonResponseObject;

    #[OA\Property(
        property: 'type',
        description: 'The type of the location (e.g., STOP, ADDRESS, PLACE)',
        ref: LocationType::class,
    )]
    public LocationType $type;

    #[OA\Property(
        property: 'tokens',
        description: 'An array of tokens representing parts of the location name or identifier',
        type: 'array',
        items: new OA\Items(type: 'string'),
    )]
    public array $tokens;

    #[OA\Property(
        property: 'name',
        description: 'The name of the location',
        type: 'string',
    )]
    public string $name;

    #[OA\Property(
        property: 'identifier',
        description: 'A unique identifier for the location',
        type: 'string',
    )]
    public string $identifier;

    #[OA\Property(
        property: 'id',
        description: 'The ID of the location',
        type: 'string',
        nullable: true,
    )]
    public ?string $id = null;

    #[OA\Property(
        property: 'lat',
        description: 'The latitude of the location',
        type: 'number',
        format: 'float',
    )]
    public float $lat;

    #[OA\Property(
        property: 'lon',
        description: 'The longitude of the location',
        type: 'number',
        format: 'float',
    )]
    public float $lon;

    #[OA\Property(
        property: 'level',
        description: 'The level or floor of the location, if applicable',
        type: 'string',
        nullable: true,
    )]
    public ?string $level = null;

    #[OA\Property(
        property: 'street',
        description: 'The street name of the location, if applicable',
        type: 'string',
        nullable: true,
    )]
    public ?string $street = null;

    #[OA\Property(
        property: 'houseNumber',
        description: 'The house number of the location, if applicable',
        type: 'string',
        nullable: true,
    )]
    public ?string $houseNumber = null;

    #[OA\Property(
        property: 'zip',
        description: 'The ZIP code of the location, if applicable',
        type: 'string',
        nullable: true,
    )]
    public ?string $zip = null;

    #[OA\Property(
        property: 'areas',
        description: 'An array of area names or identifiers associated with the location',
        type: 'array',
        items: new OA\Items(type: 'string'),
    )]
    public array $areas = [];

    #[OA\Property(
        property: 'score',
        description: 'The confidence score of the geocode result',
        type: 'number',
        format: 'float',
    )]
    public float $score = 0.0;

    #[OA\Property(
        property: 'identifiers',
        description: 'An array of location identifiers',
        type: 'array',
        items: new OA\Items(ref: LocationIdentifierDto::class),
    )]
    /** @var LocationIdentifierDto[] */
    public array $identifiers = [];

    public static function fromLocation(Location $location): GeocodeResponseEntry
    {
        $identifiers = [];
        foreach ($location->identifiers as $identifier) {
            $identifiers[$identifier->type] = new LocationIdentifierDto($identifier);
        }

        $dto = new self;

        return $dto->setType(LocationType::STOP)
            ->setName($location->name)
            ->setIdentifier($identifiers['iata']->identifier ?? $identifiers['icao']->identifier ?? $identifiers['gps']->identifier ?? $identifiers['local']->identifier ?? $location->name)
            ->setId($location->id)
            ->setLat($location->location->getLatitude())
            ->setLon($location->location->getLongitude())
            ->setIdentifiers(array_values($identifiers));
    }

    public function setType(LocationType $type): GeocodeResponseEntry
    {
        $this->type = $type;

        return $this;
    }

    public function setTokens(array $tokens): GeocodeResponseEntry
    {
        $this->tokens = $tokens;

        return $this;
    }

    public function setName(string $name): GeocodeResponseEntry
    {
        $this->name = $name;

        return $this;
    }

    public function setId(?string $id): GeocodeResponseEntry
    {
        $this->id = $id;

        return $this;
    }

    public function setIdentifier(string $identifier): GeocodeResponseEntry
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function setLat(float $lat): GeocodeResponseEntry
    {
        $this->lat = $lat;

        return $this;
    }

    public function setLon(float $lon): GeocodeResponseEntry
    {
        $this->lon = $lon;

        return $this;
    }

    public function setLevel(?string $level): GeocodeResponseEntry
    {
        $this->level = $level;

        return $this;
    }

    public function setStreet(?string $street): GeocodeResponseEntry
    {
        $this->street = $street;

        return $this;
    }

    public function setHouseNumber(?string $houseNumber): GeocodeResponseEntry
    {
        $this->houseNumber = $houseNumber;

        return $this;
    }

    public function setZip(?string $zip): GeocodeResponseEntry
    {
        $this->zip = $zip;

        return $this;
    }

    public function setAreas(array $areas): GeocodeResponseEntry
    {
        $this->areas = $areas;

        return $this;
    }

    public function setScore(float $score): GeocodeResponseEntry
    {
        $this->score = $score;

        return $this;
    }

    /**
     * @param  LocationIdentifierDto[]  $areas
     * @return $this
     */
    public function setIdentifiers(array $areas): static
    {
        $this->identifiers = $areas;

        return $this;
    }
}
