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
        $this->userHydrator = $userHydrator ?? new UserHydrator;
    }

    public function modelToDto(Post $post, bool $withGeometry = false): LocationPost|TransportPost|BasePost
    {
        if ($post->locationPost) {
            return $this->toLocationPost($post);
        }
        if ($post->transportPost) {
            return $this->toTransportPost($post, $withGeometry);
        }

        // Fallback to the base post resource if no specific type is found
        return new BasePost($post, $this->userHydrator->modelToDto($post->user));
    }

    public function toLocationPost(Post $post): LocationPost
    {
        return new LocationPost($post, $this->userHydrator->modelToDto($post->user));
    }

    public function toTransportPost(Post $post, bool $withGeometry): TransportPost
    {
        return new TransportPost($post, $this->userHydrator->modelToDto($post->user), $withGeometry);
    }
}
