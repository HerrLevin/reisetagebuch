<?php

namespace App\Dto\Notifications;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RemotePostBoostedData',
    description: 'Data for a remote post boosted notification',
    required: ['actorUsername', 'actorInstance', 'postId'],
)]
readonly class RemotePostBoostedData
{
    public function __construct(
        #[OA\Property(description: 'Username of the remote actor', type: 'string')]
        public string $actorUsername,
        #[OA\Property(description: 'Display name of the remote actor', type: 'string', nullable: true)]
        public ?string $actorDisplayName,
        #[OA\Property(description: 'Avatar URL of the remote actor', type: 'string', nullable: true)]
        public ?string $actorAvatar,
        #[OA\Property(description: 'Instance of the remote actor', type: 'string')]
        public string $actorInstance,
        #[OA\Property(description: 'ID of the boosted post', type: 'string', format: 'uuid')]
        public string $postId,
        #[OA\Property(description: 'Body of the boosted post', type: 'string', nullable: true)]
        public ?string $postBody,
    ) {}
}
