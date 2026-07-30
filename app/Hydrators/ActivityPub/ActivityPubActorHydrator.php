<?php

declare(strict_types=1);

namespace App\Hydrators\ActivityPub;

use App\Http\Resources\UserDto;
use App\Http\Resources\UserStatisticsDto;
use App\Models\ActivityPubActor;
use Illuminate\Support\Str;

class ActivityPubActorHydrator
{
    public function modelToDto(?ActivityPubActor $actor, string $actorUri, string $createdAt): UserDto
    {
        $handle = $actor?->preferred_username ?? '';
        $instanceHost = parse_url($actor?->actor_uri ?? $actorUri, PHP_URL_HOST) ?? '';
        $fullHandle = $instanceHost ? "{$handle}@{$instanceHost}" : $handle;

        $dto = new UserDto;
        $dto->id = $actor?->id ?? Str::uuid()->toString();
        $dto->name = $actor?->display_name ?? ($fullHandle ?: $actorUri);
        $dto->username = $fullHandle ?: $actorUri;
        $dto->avatar = $actor?->local_icon_url;
        $dto->profileUrl = $actor?->profile_url ?? $actorUri;
        $dto->publicKeyPem = '';
        $dto->createdAt = $createdAt;
        $dto->statistics = new UserStatisticsDto(0, 0, 0, 0, 0, 0, 0, 0, 0);

        return $dto;
    }
}
