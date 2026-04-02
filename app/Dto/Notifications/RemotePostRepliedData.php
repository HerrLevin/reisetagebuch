<?php

namespace App\Dto\Notifications;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RemotePostRepliedData',
    description: 'Data for a remote post replied notification',
    required: ['actorUsername', 'actorInstance', 'postId'],
)]
readonly class RemotePostRepliedData
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
        #[OA\Property(description: 'ID of the replied post', type: 'string', format: 'uuid')]
        public string $postId,
        #[OA\Property(description: 'Body of the replied post', type: 'string', nullable: true)]
        public ?string $postBody,
        #[OA\Property(description: 'Content of the reply', type: 'string', nullable: true)]
        public ?string $replyContent,
    ) {}
}
