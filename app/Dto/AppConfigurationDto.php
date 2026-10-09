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
        new OA\Property(
            property: 'isDebug',
            description: 'Whether debug mode (APP_DEBUG) is enabled. Should be false in production.',
            type: 'boolean',
            example: false,
        ),
        new OA\Property(
            property: 'environment',
            description: 'The configured app environment (APP_ENV). Should be "production" in production.',
            type: 'string',
            example: 'production',
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
        public bool $isDebug,
        public string $environment,
    ) {}
}
