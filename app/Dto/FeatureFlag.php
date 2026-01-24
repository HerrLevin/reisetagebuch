<?php

namespace App\Dto;

use App\Enums\Feature;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'FeatureFlag',
    required: ['name', 'enabled'],
    properties: [
        new OA\Property(
            property: 'name',
            ref: '#/components/schemas/Feature',
            description: 'The name of the feature flag',
        ),
        new OA\Property(
            property: 'enabled',
            description: 'Indicates if the feature flag is enabled',
            type: 'boolean',
            example: true,
        ),
    ],
)]
readonly class FeatureFlag
{
    public function __construct(
        public Feature $name,
        public bool $enabled,
    ) {}
}
