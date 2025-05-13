<?php

declare(strict_types=1);

namespace App\Hydrators;

use App\Http\Resources\InviteDto;
use App\Models\Invite;

class InviteHydrator
{
    public function modelToDto(Invite $user): InviteDto
    {
        $dto = new InviteDto();
        $dto->id = $user->id;
        $dto->createdAt = $user->created_at?->toIso8601String();
        $dto->expiresAt = $user->expires_at?->toIso8601String();
        $dto->usedAt = $user->used_at?->toIso8601String();

        return $dto;
    }
}
