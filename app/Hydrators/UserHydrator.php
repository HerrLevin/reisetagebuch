<?php

declare(strict_types=1);

namespace App\Hydrators;

use App\Http\Resources\UserDto;
use App\Models\User;
use Illuminate\Routing\UrlGenerator;

class UserHydrator
{
    private UrlGenerator $urlGenerator;

    public function __construct(?UrlGenerator $urlGenerator = null)
    {
        $this->urlGenerator = $urlGenerator ?? app(UrlGenerator::class);
    }

    public function modelToDto(User $user): UserDto
    {
        $dto = new UserDto();
        $dto->id = $user->id;
        $dto->name = $user->name;
        $dto->username = $user->username;
        $dto->avatar = $user->profile?->avatar ? $this->urlGenerator->to('/files/'. $user->profile?->avatar) : null;
        $dto->header = $user->profile?->header ? $this->urlGenerator->to('/files/'. $user->profile?->header) : null;
        $dto->bio = $user->profile?->bio;
        $dto->website = $user->profile?->website;
        $dto->createdAt = $user->created_at->toIso8601String();

        return $dto;
    }
}
