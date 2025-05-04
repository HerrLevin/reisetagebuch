<?php

namespace App\Http\Controllers\Inertia;

use App\Repositories\PostRepository;

class UserController extends Controller
{
    public function show(string $userId) {
        $user = auth()->user();
        $posts = new PostRepository()->dashboard($user);

        return inertia('Profile/Show', [
            'userId' => $userId,
            'posts' => $posts,
        ]);
    }
}
