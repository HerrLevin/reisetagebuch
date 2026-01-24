<?php

namespace App\Dto;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserSettingsDto',
    title: 'User Settings DTO',
    description: 'Data Transfer Object representing user settings',
    required: ['motisRadius'],
    properties: [
        new OA\Property(
            property: 'motisRadius',
            description: 'Radius for Motis suggestions in meters',
            type: 'integer',
            example: 500,
            nullable: true
        ),
    ]
)]
readonly class UserSettingsDto
{
    public function __construct(
        public ?int $motisRadius,
    ) {}
}
