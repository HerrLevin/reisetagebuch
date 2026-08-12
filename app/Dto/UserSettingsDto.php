<?php

namespace App\Dto;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserSettingsDto',
    title: 'User Settings DTO',
    description: 'Data Transfer Object representing user settings',
    required: ['motisRadius', 'requiresFollowRequest', 'hidePostsAfter'],
    properties: [
        new OA\Property(
            property: 'motisRadius',
            description: 'Radius for Motis suggestions in meters',
            type: 'integer',
            example: 500,
            nullable: true
        ),
        new OA\Property(
            property: 'requiresFollowRequest',
            description: 'Requires follow request',
            type: 'boolean',
            example: true
        ),
        new OA\Property(
            property: 'hidePostsAfter',
            description: 'Hide posts after x days',
            type: 'number',
            example: 0.25,
            nullable: true
        ),
    ]
)]
readonly class UserSettingsDto
{
    public function __construct(
        public ?int $motisRadius,
        public bool $requiresFollowRequest,
        public ?float $hidePostsAfter,
    ) {}
}
