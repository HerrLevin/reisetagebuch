<?php

namespace App\Dto\MotisApi;

use App\Models\LocationIdentifier;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AreaDto',
    description: 'Data Transfer Object for an Area in the Motis API',
    required: ['name', 'adminLevel', 'matched', 'unique', 'default'],
    type: 'object'
)]
class AreaDto
{
    #[OA\Property(
        property: 'name',
        description: 'Name of the area',
        type: 'string'
    )]
    public string $name;

    #[OA\Property(
        property: 'adminLevel',
        description: 'Administrative level of the area',
        type: 'integer'
    )]
    public int $adminLevel;

    #[OA\Property(
        property: 'matched',
        description: 'Indicates if the area was matched to a known location',
        type: 'boolean'
    )]
    public bool $matched;

    #[OA\Property(
        property: 'unique',
        description: 'Indicates if the area is unique (not shared with other locations)',
        type: 'boolean',
        nullable: true
    )]
    public ?bool $unique = null;

    #[OA\Property(
        property: 'default',
        description: 'Indicates if the area is a default area (used when no specific area is matched)',
        type: 'boolean',
        nullable: true
    )]
    public ?bool $default = null;

    public static function fromIdentifier(LocationIdentifier $identifier, bool $matched = false, bool $default = false): self
    {
        $dto = new self;
        $dto->setName($identifier->identifier)
            ->setAdminLevel(0)
            ->setMatched($matched)
            ->setDefault($default);

        return $dto;
    }

    public function setName(string $name): AreaDto
    {
        $this->name = $name;

        return $this;
    }

    public function setAdminLevel(int $adminLevel): AreaDto
    {
        $this->adminLevel = $adminLevel;

        return $this;
    }

    public function setMatched(bool $matched): AreaDto
    {
        $this->matched = $matched;

        return $this;
    }

    public function setUnique(?bool $unique): AreaDto
    {
        $this->unique = $unique;

        return $this;
    }

    public function setDefault(?bool $default): AreaDto
    {
        $this->default = $default;

        return $this;
    }
}
