<?php

namespace App\Dto;

use App\Traits\JsonResponseObject;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TokenResponseDto',
    title: 'Authenticated User Token DTO',
    description: 'Data Transfer Object representing the authenticated user',
    required: ['token', 'user'],
    properties: [
        new OA\Property(
            property: 'token',
            description: 'The access token for the authenticated user',
            type: 'string',
            example: 'eyJ'
        ),
        new OA\Property(
            property: 'user',
            ref: '#/components/schemas/AuthenticatedUserDto',
            description: 'The user'
        ),
        new OA\Property(
            property: 'expiresAt',
            description: 'The expiration time of the token',
            type: 'string',
            format: 'date-time',
            example: '2024-12-31T23:59:59Z'
        ),
    ]
)]
readonly class TokenResponseDto
{
    use JsonResponseObject;

    public function __construct(
        public string $token,
        public AuthenticatedUserDto $user,
        public Carbon $expiresAt
    ) {}
}
