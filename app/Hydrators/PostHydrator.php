<?php

declare(strict_types=1);

namespace App\Hydrators;

use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use App\Models\Post;

class PostHydrator
{
    private UserHydrator $userHydrator;

    public function __construct(?UserHydrator $userHydrator = null)
    {
        $this->userHydrator = $userHydrator ?? new UserHydrator();
    }

    public function modelToDto(Post $post): LocationPost|TransportPost|BasePost
    {
        $userDto = $this->userHydrator->modelToDto($post->user);

        if ($post->locationPost) {
            return new LocationPost($post, $userDto);
        }
        if ($post->transportPost) {
            return new TransportPost($post, $userDto);
        }

        // Fallback to the base post resource if no specific type is found
        return new BasePost($post, $userDto);
    }
}
