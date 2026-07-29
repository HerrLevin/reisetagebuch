<?php

declare(strict_types=1);

namespace App\Dto;

use App\Traits\JsonResponseObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RemoteActorProfileDto',
    required: ['actorId', 'displayName', 'preferredUsername', 'summary', 'iconUrl', 'profileUrl', 'followState'],
    properties: [
        new OA\Property(property: 'actorId', description: 'The remote actor\'s ActivityPub id', type: 'string'),
        new OA\Property(property: 'displayName', description: 'The remote actor\'s display name', type: 'string', nullable: true),
        new OA\Property(property: 'preferredUsername', description: 'The remote actor\'s preferred username', type: 'string', nullable: true),
        new OA\Property(property: 'summary', description: 'The remote actor\'s sanitized profile summary', type: 'string', nullable: true),
        new OA\Property(property: 'iconUrl', description: 'The remote actor\'s avatar URL', type: 'string', nullable: true),
        new OA\Property(property: 'profileUrl', description: 'The remote actor\'s public profile URL', type: 'string', nullable: true),
        new OA\Property(property: 'followState', description: 'The current follow state for this actor, if any', type: 'string', nullable: true),
    ],
    type: 'object',
)]
readonly class RemoteActorProfileDto
{
    use JsonResponseObject;

    public function __construct(
        public string $actorId,
        public ?string $displayName,
        public ?string $preferredUsername,
        public ?string $summary,
        public ?string $iconUrl,
        public ?string $profileUrl,
        public ?string $followState,
    ) {}
}
