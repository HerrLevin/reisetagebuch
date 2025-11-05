<?php

namespace App\Http\Resources\PostTypes;

use App\Enums\Visibility;
use App\Http\Resources\UserDto;
use App\Models\Post;
use Carbon\Carbon;

class BasePost
{
    public string $id;

    public UserDto $user;

    public ?string $body = null;

    public Visibility $visibility;

    public string $published_at;

    public string $created_at;

    public string $updated_at;

    /** @var string[] */
    public array $hashTags = [];

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
    }
}
