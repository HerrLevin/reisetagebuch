<?php

namespace App\Dto\MotisApi;

use App\Http\Resources\LocationIdentifierDto;
use App\Models\Location;

class GeocodeResponseEntry
{
    public LocationType $type;

    public array $tokens;

    public string $name;

    public string $identifier;

    public ?string $id = null;

    public float $lat;

    public float $lon;

    public ?string $level = null;

    public ?string $street = null;

    public ?string $houseNumber = null;

    public ?string $zip = null;

    public array $areas = [];

    public float $score = 0.0;

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
