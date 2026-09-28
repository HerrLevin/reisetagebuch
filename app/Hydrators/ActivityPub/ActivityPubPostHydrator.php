<?php

declare(strict_types=1);

namespace App\Hydrators\ActivityPub;

use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use App\Http\Resources\UserDto;
use App\Http\Resources\UserStatisticsDto;
use App\Models\ActivityPubPost;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ActivityPubPostHydrator
{
    public function modelToDto(ActivityPubPost $post): BasePost
    {
        $userDto = $this->buildUserDto($post);

        try {
            return match ($post->extension_data['postType'] ?? null) {
                'location' => LocationPost::fromRemote($post, $userDto, $post->extension_data['location'] ?? []),
                'transport' => TransportPost::fromRemote($post, $userDto, $post->extension_data['transport'] ?? []),
                default => BasePost::fromRemote($post, $userDto),
            };
        } catch (Throwable $e) {
            Log::warning('Failed to reconstruct DTO from stored RTB extension_data, falling back to plain post', [
                'postId' => $post->id,
                'error' => $e->getMessage(),
            ]);

            return BasePost::fromRemote($post, $userDto);
        }
    }

    private function buildUserDto(ActivityPubPost $post): UserDto
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

        return $userDto;
    }
}
