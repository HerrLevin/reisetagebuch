<?php

namespace App\Dto;

use App\Traits\JsonResponseObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PrivacyPolicyDto',
    title: 'Privacy Policy DTO',
    description: 'Data Transfer Object representing a versioned privacy policy',
    required: ['id', 'content', 'validFrom'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'The unique identifier of this privacy policy version',
            type: 'string',
            format: 'uuid',
        ),
        new OA\Property(
            property: 'content',
            description: 'The privacy policy content, rendered as free-form text',
            type: 'string',
        ),
        new OA\Property(
            property: 'validFrom',
            description: 'The point in time from which this version is (or will be) in effect',
            type: 'string',
            format: 'date-time',
        ),
        new OA\Property(
            property: 'acceptedAt',
            description: 'When the requesting user accepted this version, if authenticated and accepted',
            type: 'string',
            format: 'date-time',
            nullable: true,
        ),
    ],
)]
readonly class PrivacyPolicyDto
{
    use JsonResponseObject;

    public function __construct(
        public string $id,
        public string $content,
        public string $validFrom,
        public ?string $acceptedAt = null,
    ) {}
}
