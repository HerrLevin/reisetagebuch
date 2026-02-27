<?php

namespace App\Dto;

use App\Traits\JsonResponseObject;
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
    ]
)]
readonly class TokenResponseDto
{
    use JsonResponseObject;

    public function __construct(
        public string $token,
        public AuthenticatedUserDto $user,
    ) {}
}
