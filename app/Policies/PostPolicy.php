<?php

namespace App\Policies;

use App\Http\Resources\PostTypes\BasePost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Post $post): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Post $post): Response
    {
        return $user->id === $post->user_id
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
