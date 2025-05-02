<?php

namespace App\Http\Resources\PostTypes;

use App\Models\Post;

class BasePost
{
    public string $id;
    public UserDto $user;
    public ?string $body = null;
    public string $created_at;
    public string $updated_at;

    public function __construct(Post $post)
    {
        $this->id = $post->id;
        $this->user = new UserDto($post->user->id, $post->user->name);
        $this->body = $post->body;
        $this->created_at = $post->created_at;
        $this->updated_at = $post->updated_at;
    }
}
