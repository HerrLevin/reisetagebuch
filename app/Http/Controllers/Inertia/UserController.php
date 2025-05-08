<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Backend\PostController;
use App\Http\Controllers\Backend\UserController as BackendUserController;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
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
            'posts' => Inertia::merge($posts->items),
            'nextCursor' => $posts->nextCursor,
            'previousCursor' => $posts->previousCursor,
            'user' => $user,
        ]);
    }

    public function update(UpdateProfileRequest $username): RedirectResponse|Response|ResponseFactory
    {
        $user = $this->userController->update($username);

        if (route('profile.show', $user->username) !== url()->previous()) {
            return redirect()->route('profile.show', $user->username);
        }

        return inertia('Profile/Show', [
            'userId' => $user->id,
            'posts' => $this->postController->postsForUser($user->id),
            'user' => $user,
        ]);
    }
}
