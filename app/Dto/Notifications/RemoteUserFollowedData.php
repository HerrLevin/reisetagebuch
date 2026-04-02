<?php

namespace App\Dto\Notifications;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RemoteUserFollowedData',
    description: 'Data for a remote user followed notification',
    required: ['actorUsername', 'actorInstance'],
)]
readonly class RemoteUserFollowedData
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
    ) {}
}
