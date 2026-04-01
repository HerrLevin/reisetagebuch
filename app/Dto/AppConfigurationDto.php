<?php

namespace App\Dto;

use App\Traits\JsonResponseObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AppConfigurationDto',
    required: ['appName', 'featureFlags'],
    properties: [
        new OA\Property(
            property: 'appName',
            description: 'The name of the application',
            type: 'string',
            example: 'Reisetagebuch',
        ),
        new OA\Property(
            property: 'featureFlags',
            description: 'List of feature flags and their statuses',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/FeatureFlag'),
        ),
        new OA\Property(
            property: 'appVersion',
            description: 'The version of the application',
            type: 'string',
            example: '1.0.0',
        ),
    ],
)]
readonly class AppConfigurationDto
{
    use JsonResponseObject;

    public function __construct(
        public string $appName,
        public string $appVersion,
        public array $featureFlags,
    ) {}
}
