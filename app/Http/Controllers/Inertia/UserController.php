<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Backend\UserController as BackendUserController;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
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
        return inertia('Profile/Show', ['username' => $username]);
    }

    public function showMap(string $username): Response|ResponseFactory
    {
        return inertia('Profile/ShowMap', [
            'username' => $username,
        ]);
    }

    public function update(UpdateProfileRequest $username): RedirectResponse|Response|ResponseFactory
    {
        $user = $this->userController->update($username);

        if (route('profile.show', $user->username) !== url()->previous()) {
            return redirect()->route('profile.show', $user->username);
        }

        return inertia('Profile/Show', ['username' => $username]);
    }
}
