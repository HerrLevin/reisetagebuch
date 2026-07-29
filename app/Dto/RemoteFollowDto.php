<?php

declare(strict_types=1);

namespace App\Dto;

use App\Traits\JsonResponseObject;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RemoteFollowDto',
    required: ['actorId', 'state', 'createdAt', 'displayName', 'preferredUsername', 'iconUrl', 'profileUrl'],
    properties: [
        new OA\Property(property: 'actorId', description: 'The remote actor\'s ActivityPub id', type: 'string'),
        new OA\Property(property: 'state', description: 'The state of the follow request', type: 'string'),
        new OA\Property(property: 'createdAt', description: 'When the follow request was created', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'displayName', description: 'The remote actor\'s display name', type: 'string', nullable: true),
        new OA\Property(property: 'preferredUsername', description: 'The remote actor\'s preferred username', type: 'string', nullable: true),
        new OA\Property(property: 'iconUrl', description: 'The remote actor\'s avatar URL', type: 'string', nullable: true),
        new OA\Property(property: 'profileUrl', description: 'The remote actor\'s public profile URL', type: 'string', nullable: true),
    ],
    type: 'object',
)]
readonly class RemoteFollowDto
{
    use JsonResponseObject;

    public function __construct(
        public string $actorId,
        public string $state,
        public ?Carbon $createdAt,
        public ?string $displayName,
        public ?string $preferredUsername,
        public ?string $iconUrl,
        public ?string $profileUrl,
    ) {}
}
