<?php

namespace App\Http\Resources;

use App\Dto\ActivityPub\Extensions\RtbTagData;
use App\Models\LocationTag;
use OpenApi\Attributes as OpenApi;

#[OpenApi\Schema(
    schema: 'LocationTagDto',
    description: 'Location Tag Data Object',
    required: ['key', 'value'],
    type: 'object'
)]
class LocationTagDto
{
    #[OpenApi\Property('key', description: 'Key of the location tag', type: 'string')]
    public string $key;

    #[OpenApi\Property('value', description: 'Value of the location tag', type: 'string')]
    public string $value;

    public function __construct(?LocationTag $locationTag = null)
    {
        if ($locationTag === null) {
            return;
        }

        $this->key = $locationTag->key;
        $this->value = $locationTag->value;
    }

    public static function fromRtb(RtbTagData $data): self
    {
        $dto = new self;
        $dto->key = $data->key;
        $dto->value = $data->value;

        return $dto;
    }
}
