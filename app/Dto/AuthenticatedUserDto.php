<?php

namespace App\Dto;

use App\Traits\JsonResponseObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthenticatedUserDto',
    title: 'Authenticated User DTO',
    description: 'Data Transfer Object representing the authenticated user',
    required: ['id', 'name', 'username', 'email', 'mustVerifyEmail', 'canInviteUsers', 'traewellingConnected', 'settings', 'avatar'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'The unique identifier of the user',
            type: 'string',
            format: 'uuid',
        ),
        new OA\Property(
            property: 'name',
            description: 'The full name of the user',
            type: 'string',
            example: 'John Doe'
        ),
        new OA\Property(
            property: 'username',
            description: 'The username of the user',
            type: 'string',
            example: 'john_doe',
        ),
        new OA\Property(
            property: 'email',
            description: 'The email address of the user',
            type: 'string',
            format: 'email',
            example: 'user@reisetagebu.ch',
        ),
        new OA\Property(
            property: 'avatar',
            description: 'The URL of the user avatar image',
            type: 'string',
            format: 'uri',
            example: 'https://example.com/avatars/john_doe.png',
            nullable: true
        ),
        new OA\Property(
            property: 'mustVerifyEmail',
            description: 'Indicates whether the user must verify their email address',
            type: 'boolean',
            example: false
        ),
        new OA\Property(
            property: 'canInviteUsers',
            description: 'Indicates whether the user has permission to invite other users',
            type: 'boolean',
            example: true
        ),
        new OA\Property(
            property: 'traewellingConnected',
            description: 'Indicates whether the user has connected their Traewelling account',
            type: 'boolean',
            example: false
        ),
        new OA\Property(
            property: 'settings',
            ref: '#/components/schemas/UserSettingsDto',
            description: 'The user settings'
        ),
    ]
)]
readonly class AuthenticatedUserDto
{
    use JsonResponseObject;

    public function __construct(
        public string $id,
        public string $name,
        public ?string $username,
        public string $email,
        public ?string $avatar,
        public bool $mustVerifyEmail,
        public UserSettingsDto $settings,
        public bool $canInviteUsers = false,
        public bool $traewellingConnected = false,
    ) {}
}
