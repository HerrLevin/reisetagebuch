<?php

declare(strict_types=1);

namespace App\Http\Resources;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'InviteDto',
    required: ['id', 'createdAt', 'expiresAt', 'usedAt'],
    properties: [
        new OA\Property(property: 'id', description: 'The unique identifier of the invite code', type: 'string'),
        new OA\Property(property: 'createdAt', description: 'The creation timestamp of the invite code', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'expiresAt', description: 'The expiration timestamp of the invite code', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'usedAt', description: 'The timestamp when the invite code was used', type: 'string', format: 'date-time', nullable: true),
    ],
    type: 'object'
)]
class InviteDto
{
    public string $id;

    public ?string $createdAt;

    public ?string $expiresAt;

    public ?string $usedAt;
}
