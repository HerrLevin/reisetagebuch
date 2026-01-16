<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserDto;
use App\Models\User;
use App\Repositories\FileRepository;
use App\Repositories\UserRepository;

class UserController extends Controller
{
    private UserRepository $userRepository;

    private FileRepository $fileRepository;

    public function __construct(UserRepository $userRepository, FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
        $this->userRepository = $userRepository;
    }

    public function show(string $username): UserDto
    {
        return $this->userRepository->getUserByUsername($username);
    }

    public function update(UpdateProfileRequest $request, User $user): UserDto
    {
        $avatarPath = $user->profile?->avatar;
        $headerPath = $user->profile?->header;

        if ($request->hasFile('avatar')) {
            $upload = $request->file('avatar');
            $avatarPath = $this->fileRepository->uploadAndReplaceFile('avatars', $upload, $avatarPath);
        }

        if ($request->deleteAvatar) {
            $this->fileRepository->deleteFile($user->profile->avatar);
            $avatarPath = null;
        }

        if ($request->hasFile('header')) {
            $upload = $request->file('header');
            $headerPath = $this->fileRepository->uploadAndReplaceFile('headers', $upload, $headerPath);
        }

        if ($request->deleteHeader) {
            $this->fileRepository->deleteFile($user->profile->header);
            $headerPath = null;
        }

        return $this->userRepository->updateUser(
            $user,
            $request->name,
            $request->bio,
            $request->website,
            $avatarPath,
            $headerPath
        );
    }
}
