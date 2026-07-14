<?php

namespace App\Dto\Notifications;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ActivityPubMentionData',
    description: 'Data for an ActivityPub mention notification',
    required: [
        'actorId',
        'preferredUsername',
        'postId',
    ],
    properties: [
        new OA\Property(
            property: 'actorId',
            description: 'ActivityPub actor ID (URI) of the remote user who mentioned the local user',
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
            description: 'Local ID of the AP post containing the mention',
            type: 'string',
            format: 'uuid'
        ),
        new OA\Property(
            property: 'postBody',
            description: 'Truncated body of the post containing the mention',
            type: 'string',
            nullable: true
        ),
    ]
)]
readonly class ActivityPubMentionData
{
    public function __construct(
        public string $actorId,
        public string $preferredUsername,
        public ?string $displayName,
        public ?string $iconUrl,
        public ?string $profileUrl,
        public string $postId,
        public ?string $postBody,
    ) {}
}
