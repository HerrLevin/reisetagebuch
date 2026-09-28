<?php

namespace App\Http\Resources;

use App\Dto\ActivityPub\Extensions\RtbIdentifierData;
use App\Models\LocationIdentifier;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LocationIdentifierDto',
    description: 'Location Identifier Data Object',
    required: ['type', 'origin', 'identifier'],
    type: 'object'
)]
class LocationIdentifierDto
{
    #[OA\Property('type', description: 'Type of the location identifier', type: 'string')]
    public string $type;

    #[OA\Property('origin', description: 'Origin of the location identifier', type: 'string')]
    public string $origin;

    #[OA\Property('identifier', description: 'The location identifier value', type: 'string')]
    public string $identifier;

    public function __construct(?LocationIdentifier $locationTag = null)
    {
        if ($locationTag === null) {
            return;
        }

        $this->type = $locationTag->type;
        $this->origin = $locationTag->origin;
        $this->identifier = $locationTag->identifier;
    }

    public static function fromRtb(RtbIdentifierData $data): self
    {
        $dto = new self;
        $dto->type = $data->type;
        $dto->origin = $data->origin;
        $dto->identifier = $data->identifier;

        return $dto;
    }
}
