<?php

namespace App\Policies;

use App\Enums\Visibility;
use App\Http\Resources\PostTypes\BasePost;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(?User $user, BasePost $post): bool
    {
        if ($post->visibility === Visibility::PRIVATE) {
            return $user?->id === $post->user->id;
        }

        if ($post->visibility === Visibility::ONLY_AUTHENTICATED) {
            return $user !== null;
        }

        return in_array($post->visibility, [Visibility::PUBLIC, Visibility::UNLISTED], true);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, BasePost $post): Response
    {
        return $user->id === $post->user->id
            ? Response::allow()
            : Response::deny('You do not own this post.');
    }

    public function delete(User $user, BasePost $post): Response
    {
        return $user->id === $post->user->id
            ? Response::allow()
            : Response::deny('You do not own this post.');
    }
}
