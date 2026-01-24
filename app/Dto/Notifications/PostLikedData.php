<?php

namespace App\Dto\Notifications;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PostLikedData',
    description: 'Data for a post liked notification',
    required: [
        'postId',
        'postBody',
        'likedByUserId',
        'likedByUserName',
        'likedByUserDisplayName',
        'likedByUserAvatarUrl',
        'postSummary',
    ],
    properties: [
        new OA\Property(
            property: 'postId',
            description: 'ID of the liked post',
            type: 'string',
            format: 'uuid'
        ),
        new OA\Property(
            property: 'postBody',
            description: 'Body content of the liked post',
            type: 'string',
            format: 'text'
        ),
        new OA\Property(
            property: 'likedByUserId',
            description: 'ID of the user who liked the post',
            type: 'string',
            format: 'uuid'
        ),
        new OA\Property(
            property: 'likedByUserName',
            description: 'Username of the user who liked the post',
            type: 'string',
            example: 'johndoe'
        ),
        new OA\Property(
            property: 'likedByUserDisplayName',
            description: 'Display name of the user who liked the post',
            type: 'string',
            example: 'John Doe'
        ),
        new OA\Property(
            property: 'likedByUserAvatarUrl',
            description: 'Avatar URL of the user who liked the post',
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
readonly class PostLikedData
{
    public function __construct(
        public string $postId,
        public ?string $postBody,
        public string $likedByUserId,
        public string $likedByUserName,
        public string $likedByUserDisplayName,
        public ?string $likedByUserAvatarUrl,
        public ?string $postSummary = null,
    ) {}
}
