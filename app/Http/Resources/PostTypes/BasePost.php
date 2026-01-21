<?php

namespace App\Http\Resources\PostTypes;

use App\Enums\Visibility;
use App\Http\Resources\UserDto;
use App\Models\Post;
use App\Traits\JsonResponseObject;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BasePost',
    description: 'Base Post Resource',
    required: ['id', 'user', 'body', 'visibility', 'publishedAt', 'createdAt', 'updatedAt', 'likesCount', 'likedByUser', 'metaInfos', 'hashTags'],
    type: 'object'
)]
class BasePost
{
    use JsonResponseObject;

    #[OA\Property('id', description: 'Post ID', type: 'string', format: 'uuid')]
    public string $id;

    #[OA\Property(
        'user',
        ref: UserDto::class,
        description: 'User who created the post',
    )]
    public UserDto $user;

    #[OA\Property('body', description: 'Post body content', type: 'string', nullable: true)]
    public ?string $body = null;

    #[OA\Property(
        'visibility',
        ref: Visibility::class,
        description: 'Post visibility level',
    )]
    public Visibility $visibility;

    #[OA\Property('publishedAt', description: 'Post published at timestamp', type: 'string', format: 'date-time')]
    public string $publishedAt;

    #[OA\Property('createdAt', description: 'Post created at timestamp', type: 'string', format: 'date-time')]
    public string $createdAt;

    #[OA\Property('updatedAt', description: 'Post updated at timestamp', type: 'string', format: 'date-time')]
    public string $updatedAt;

    #[OA\Property(
        'hashTags',
        description: 'List of hashtags associated with the post',
        type: 'array',
        items: new OA\Items(type: 'string')
    )]
    /** @var string[] */
    public array $hashTags = [];

    #[OA\Property('likesCount', type: 'integer', description: 'Number of likes on the post')]
    public int $likesCount = 0;

    #[OA\Property('likedByUser', type: 'boolean', description: 'Indicates if the post is liked by the current user')]
    public bool $likedByUser = false;

    #[OA\Property(
        'metaInfos',
        description: 'Additional meta information associated with the post',
        type: 'object',
        additionalProperties: new OA\AdditionalProperties(
            oneOf: [
                new OA\Schema(type: 'string'),
                new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
            ]
        )
    )]
    /** @var array<string, string|string[]> */
    public array $metaInfos = [];

    public function __construct(Post $post, UserDto $userDto)
    {
        $this->id = $post->id;
        $this->body = $post->body;
        $this->user = $userDto;
        $this->visibility = $post->visibility;
        $this->publishedAt = $post->published_at?->toIso8601String() ?? Carbon::now()->toIso8601String();
        $this->createdAt = $post->created_at->toIso8601String();
        $this->updatedAt = $post->updated_at->toIso8601String();
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
