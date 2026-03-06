<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImageUploadRequest;
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

    public function updateAvatar(ImageUploadRequest $request, User $user): UserDto
    {
        $upload = $request->file('image');
        $avatarPath = $user->profile?->avatar;
        $avatarPath = $this->fileRepository->uploadAndReplaceFile('avatars', $upload, $avatarPath);

        return $this->userRepository->updateAvatar($user, $avatarPath);
    }

    public function deleteAvatar(User $user): UserDto
    {
        if ($user->profile?->avatar) {
            $this->fileRepository->deleteFile($user->profile->avatar);
        }

        return $this->userRepository->updateAvatar($user, null);
    }

    public function updateHeader(ImageUploadRequest $request, User $user): UserDto
    {
        $upload = $request->file('image');
        $headerPath = $user->profile?->header;
        $headerPath = $this->fileRepository->uploadAndReplaceFile('headers', $upload, $headerPath);

        return $this->userRepository->updateHeader($user, $headerPath);
    }

    public function deleteHeader(User $user): UserDto
    {
        if ($user->profile?->header) {
            $this->fileRepository->deleteFile($user->profile->header);
        }

        return $this->userRepository->updateHeader($user, null);
    }

    public function update(UpdateProfileRequest $request, User $user): UserDto
    {
        return $this->userRepository->updateUser(
            $user,
            $request->name,
            $request->bio,
            $request->website
        );
    }
}
