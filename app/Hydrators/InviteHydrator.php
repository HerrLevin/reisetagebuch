<?php

declare(strict_types=1);

namespace App\Hydrators;

use App\Http\Resources\InviteDto;
use App\Models\Invite;

class InviteHydrator
{
    public function modelToDto(Invite $invite): InviteDto
    {
        $dto = new InviteDto();
        $dto->id = $invite->id;
        $dto->createdAt = $invite->created_at?->toIso8601String();
        $dto->expiresAt = $invite->expires_at?->toIso8601String();
        $dto->usedAt = $invite->used_at?->toIso8601String();

        return $dto;
    }
}
