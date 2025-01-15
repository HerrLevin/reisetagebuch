<?php

namespace App\Http\Resources\PostTypes;

use App\Enums\Visibility;
use App\Http\Resources\UserDto;
use App\Models\Post;
use App\Traits\JsonResponseObject;
use Carbon\Carbon;

class BasePost
{
    use JsonResponseObject;

    public string $id;

    public UserDto $user;

    public ?string $body = null;

    public Visibility $visibility;

    public string $published_at;

    public string $created_at;

    public string $updated_at;

    /** @var string[] */
    public array $hashTags = [];

    public int $likesCount = 0;

    public bool $likedByUser = false;

    /** @var array<string, mixed> */
    public array $metaInfos = [];

    public function __construct(Post $post, UserDto $userDto)
    {
        $this->id = $post->id;
        $this->body = $post->body;
        $this->user = $userDto;
        $this->visibility = $post->visibility;
        $this->published_at = $post->published_at?->toIso8601String() ?? Carbon::now()->toIso8601String();
        $this->created_at = $post->created_at->toIso8601String();
        $this->updated_at = $post->updated_at->toIso8601String();
        $this->hashTags = $post->hashTags?->map(fn ($hashTag) => $hashTag->value)->toArray() ?? [];
        $this->likesCount = $post->likes_count ?? 0;
        $this->likedByUser = $post->liked_by_user ?? false;
        $this->metaInfos = $post->metaInfos?->groupBy(fn ($metaInfo) => $metaInfo->key->value)
            ->map(function ($group) {
                if ($group->first()->key->valueType() === \App\Enums\PostMetaInfo\MetaInfoValueType::STRING_LIST) {
                    return $group->sortBy('order')->pluck('value')->values()->toArray();
                }

                return $group->first()->value;
            })->toArray() ?? [];
    }
}
