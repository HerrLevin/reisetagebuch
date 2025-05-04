<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserDto;
use App\Repositories\UserRepository;

class UserController extends Controller
{
    private UserRepository $userRepository;
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function show(string $username): UserDto
    {
        return $this->userRepository->getUserByUsername($username);
    }

    public function update(UpdateProfileRequest $request): UserDto
    {
        $user = auth()->user();

        return $this->userRepository->updateUser(
            $user,
            $request->name,
            $request->bio,
            $request->website
        );
    }
}
