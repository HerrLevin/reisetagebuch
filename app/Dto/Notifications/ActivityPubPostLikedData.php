<?php

namespace App\Dto\Notifications;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ActivityPubPostLikedData',
    description: 'Data for an ActivityPub post liked notification',
    required: [
        'actorId',
        'preferredUsername',
        'postId',
    ],
    properties: [
        new OA\Property(
            property: 'actorId',
            description: 'ActivityPub actor ID (URI) of the remote user who liked the post',
            type: 'string',
            format: 'uri'
        ),
        new OA\Property(
            property: 'preferredUsername',
            description: 'Preferred username of the remote user',
            type: 'string',
            example: 'johndoe'
        ),
        new OA\Property(
            property: 'displayName',
            description: 'Display name of the remote user',
            type: 'string',
            example: 'John Doe',
            nullable: true
        ),
        new OA\Property(
            property: 'iconUrl',
            description: 'Avatar URL of the remote user',
            type: 'string',
            format: 'uri',
            nullable: true
        ),
        new OA\Property(
            property: 'profileUrl',
            description: 'Profile URL of the remote user',
            type: 'string',
            format: 'uri',
            nullable: true
        ),
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
            nullable: true
        ),
        new OA\Property(
            property: 'postSummary',
            description: 'Summary of the liked post',
            type: 'string',
            nullable: true
        ),
    ]
)]
readonly class ActivityPubPostLikedData
{
    public function __construct(
        public string $actorId,
        public string $preferredUsername,
        public ?string $displayName,
        public ?string $iconUrl,
        public ?string $profileUrl,
        public ?string $postId,
        public ?string $postBody,
        public ?string $postSummary,
    ) {}
}
