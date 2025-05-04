<?php

namespace App\Http\Resources\PostTypes;

use App\Http\Resources\UserDto;
use App\Models\Post;

class BasePost
{
    public string $id;
    public UserDto $user;
    public ?string $body = null;
    public string $created_at;
    public string $updated_at;

    public function __construct(Post $post, UserDto $userDto)
    {
        $this->id = $post->id;
        $this->body = $post->body;
        $this->user = $userDto;
        $this->created_at = $post->created_at->toIso8601String();
        $this->updated_at = $post->updated_at->toIso8601String();
    }
}
