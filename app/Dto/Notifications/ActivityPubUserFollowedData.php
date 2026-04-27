<?php

namespace App\Dto\Notifications;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ActivityPubUserFollowedData',
    description: 'Data for an ActivityPub user followed notification',
    required: [
        'followerActorId',
        'followerPreferredUsername',
    ],
    properties: [
        new OA\Property(
            property: 'followerActorId',
            description: 'ActivityPub actor ID (URI) of the remote follower',
            type: 'string',
            format: 'uri'
        ),
        new OA\Property(
            property: 'followerPreferredUsername',
            description: 'Preferred username of the remote follower',
            type: 'string',
            example: 'johndoe'
        ),
        new OA\Property(
            property: 'followerDisplayName',
            description: 'Display name of the remote follower',
            type: 'string',
            example: 'John Doe',
            nullable: true
        ),
        new OA\Property(
            property: 'followerIconUrl',
            description: 'Avatar URL of the remote follower',
            type: 'string',
            format: 'uri',
            nullable: true
        ),
        new OA\Property(
            property: 'followerProfileUrl',
            description: 'Profile URL of the remote follower',
            type: 'string',
            format: 'uri',
            nullable: true
        ),
    ]
)]
readonly class ActivityPubUserFollowedData
{
    public function __construct(
        public string $followerActorId,
        public string $followerPreferredUsername,
        public ?string $followerDisplayName,
        public ?string $followerIconUrl,
        public ?string $followerProfileUrl,
    ) {}
}
