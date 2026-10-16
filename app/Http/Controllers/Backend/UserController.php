<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserDto;
use App\Jobs\ActivityPub\PushProfileUpdateToMastodon;
use App\Models\User;
use App\Repositories\FileRepository;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Collection;

class UserController extends Controller
{
    private UserRepository $userRepository;

    private FileRepository $fileRepository;

    private PostRepository $postRepository;

    public function __construct(UserRepository $userRepository, FileRepository $fileRepository, PostRepository $postRepository)
    {
        $this->fileRepository = $fileRepository;
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
    }

    public function show(string $username): UserDto
    {
        return $this->userRepository->getUserByUsername($username);
    }

    /**
     * @return Collection<int, mixed> raw `locations.location` geometry values, scoped to what $visitingUser may see
     */
    public function mapData(string $userId, ?User $visitingUser = null): Collection
    {
        return $this->postRepository->getMapDataForUserId($userId, $visitingUser);
    }

    /**
     * @return UserDto[]
     */
    public function search(string $query): array
    {
        return $this->userRepository->searchUsers($query);
    }

    public function updateAvatar(ImageUploadRequest $request, User $user): UserDto
    {
        $upload = $request->file('image');
        $avatarPath = $user->profile?->avatar;
        $avatarPath = $this->fileRepository->uploadAndReplaceFile('avatars', $upload, $avatarPath);

        $userDto = $this->userRepository->updateAvatar($user, $avatarPath, $upload->getMimeType());
        PushProfileUpdateToMastodon::dispatch($user->id);

        return $userDto;
    }

    public function deleteAvatar(User $user): UserDto
    {
        if ($user->profile?->avatar) {
            $this->fileRepository->deleteFile($user->profile->avatar);
        }

        $userDto = $this->userRepository->updateAvatar($user, null, null);
        PushProfileUpdateToMastodon::dispatch($user->id);

        return $userDto;
    }

    public function updateHeader(ImageUploadRequest $request, User $user): UserDto
    {
        $upload = $request->file('image');
        $headerPath = $user->profile?->header;
        $headerPath = $this->fileRepository->uploadAndReplaceFile('headers', $upload, $headerPath);

        $userDto = $this->userRepository->updateHeader($user, $headerPath, $upload->getMimeType());
        PushProfileUpdateToMastodon::dispatch($user->id);

        return $userDto;
    }

    public function deleteHeader(User $user): UserDto
    {
        if ($user->profile?->header) {
            $this->fileRepository->deleteFile($user->profile->header);
        }

        $userDto = $this->userRepository->updateHeader($user, null, null);
        PushProfileUpdateToMastodon::dispatch($user->id);

        return $userDto;
    }

    public function update(UpdateProfileRequest $request, User $user): UserDto
    {
        $userDto = $this->userRepository->updateUser(
            $user,
            $request->name,
            $request->bio,
            $request->website
        );
        PushProfileUpdateToMastodon::dispatch($user->id);

        return $userDto;
    }
}
