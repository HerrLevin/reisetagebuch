<?php

namespace App\Dto\Notifications;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserFollowedData',
    description: 'Data for a user followed notification',
    required: [
        'followerUserId',
        'followerUserName',
        'followerUserDisplayName',
        'followerUserAvatarUrl',
    ],
    properties: [
        new OA\Property(
            property: 'followerUserId',
            description: 'ID of the user who followed you',
            type: 'string',
            format: 'uuid'
        ),
        new OA\Property(
            property: 'followerUserName',
            description: 'Username of the user who followed you',
            type: 'string',
            example: 'johndoe'
        ),
        new OA\Property(
            property: 'followerUserDisplayName',
            description: 'Display name of the user who followed you',
            type: 'string',
            example: 'John Doe'
        ),
        new OA\Property(
            property: 'followerUserAvatarUrl',
            description: 'Avatar URL of the user who followed you',
            type: 'string',
            format: 'uri'
        ),
        new OA\Property(
            property: 'postSummary',
            description: 'Optional summary of the liked post',
            type: 'string',
            nullable: true
        ),
    ]
)]
readonly class UserFollowedData
{
    public function __construct(
        public string $followerUserId,
        public string $followerUserName,
        public string $followerUserDisplayName,
        public ?string $followerUserAvatarUrl,
    ) {}
}
