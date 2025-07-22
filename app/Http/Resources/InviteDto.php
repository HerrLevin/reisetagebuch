<?php

declare(strict_types=1);

namespace App\Http\Resources;

class InviteDto
{
    public string $id;

    public ?string $createdAt;

    public ?string $expiresAt;

    public ?string $usedAt;
}
