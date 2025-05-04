<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Backend\PostController;
use App\Http\Controllers\Backend\UserController as BackendUserController;
use Inertia\Response;
use Inertia\ResponseFactory;

class UserController extends Controller
{
    private BackendUserController $userController;
    private PostController $postController;

    public function __construct(BackendUserController $userController, PostController $postController)
    {
        $this->postController = $postController;
        $this->userController = $userController;
    }

    public function show(string $username): Response|ResponseFactory
    {
        $user = $this->userController->show($username);
        $posts = $this->postController->postsForUser($user->id);


        return inertia('Profile/Show', [
            'userId' => $user->id,
            'posts' => $posts,
            'user' => $user,
        ]);
    }
}
