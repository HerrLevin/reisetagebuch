<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Backend\UserController as BackendUserController;
use App\Repositories\PostRepository;
use Inertia\Response;
use Inertia\ResponseFactory;

class UserController extends Controller
{
    private BackendUserController $userController;

    public function __construct(BackendUserController $userController)
    {
        $this->userController = $userController;
    }

    public function show(string $username): Response|ResponseFactory
    {
        $user = $this->userController->show($username);

        $posts = new PostRepository()->dashboard($user->id);

        return inertia('Profile/Show', [
            'userId' => $user->id,
            'posts' => $posts,
            'user' => $user,
        ]);
    }
}
