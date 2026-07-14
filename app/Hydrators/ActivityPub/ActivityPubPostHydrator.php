<?php

declare(strict_types=1);

namespace App\Hydrators\ActivityPub;

use App\Enums\Visibility;
use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\UserDto;
use App\Http\Resources\UserStatisticsDto;
use App\Models\ActivityPubPost;
use Illuminate\Support\Str;

class ActivityPubPostHydrator
{
    public function modelToDto(ActivityPubPost $post): BasePost
    {
        $actor = $post->actor;
        $handle = $actor?->preferred_username ?? '';
        $instanceHost = $actor ? (parse_url($actor->actor_uri, PHP_URL_HOST) ?? '') : '';
        $fullHandle = $instanceHost ? "{$handle}@{$instanceHost}" : $handle;

        $userDto = new UserDto;
        $userDto->id = $actor?->id ?? Str::uuid()->toString();
        $userDto->name = $actor?->display_name ?? $fullHandle;
        $userDto->username = $fullHandle;
        $userDto->avatar = $actor?->local_icon_url;
        $userDto->profileUrl = $actor?->profile_url;
        $userDto->publicKeyPem = '';
        $userDto->createdAt = $post->created_at->toIso8601String();
        $userDto->statistics = new UserStatisticsDto(0, 0, 0, 0, 0, 0, 0, 0, 0);

        $dto = new BasePost;
        $dto->id = $post->id;
        $dto->user = $userDto;
        $dto->body = $post->content;
        $dto->visibility = Visibility::PUBLIC;
        $dto->sourceUrl = $post->url ?? $post->activity_id;
        $dto->publishedAt = $post->published_at->toIso8601String();
        $dto->createdAt = $post->created_at->toIso8601String();
        $dto->updatedAt = $post->updated_at->toIso8601String();
        $dto->likesCount = $post->likes_count ?? 0;
        $dto->likedByUser = (bool) ($post->liked_by_user ?? false);

        return $dto;
    }
}
