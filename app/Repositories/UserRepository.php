<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Http\Resources\UserDto;
use App\Hydrators\UserHydrator;
use App\Models\User;
use JetBrains\PhpStorm\ArrayShape;

class UserRepository
{
    private UserHydrator $userHydrator;

    public function __construct(?UserHydrator $userHydrator = null)
    {
        $this->userHydrator = $userHydrator ?? new UserHydrator;
    }

    public function getUserByUsername(string $username): UserDto
    {
        $user = User::with('profile')->where('username', $username)->firstOrFail();

        return $this->userHydrator->modelToDto($user);
    }

    public function getUserById(string $userId): UserDto
    {
        $user = User::with('profile')->findOrFail($userId);

        return $this->userHydrator->modelToDto($user);
    }

    public function updateAvatar(User $user, ?string $avatarPath, ?string $avatarMimeType): UserDto
    {
        if (! $user->profile) {
            $user->profile()->create(['avatar' => $avatarPath, 'avatar_mime_type' => $avatarMimeType]);
        } else {
            $user->profile()->update(['avatar' => $avatarPath, 'avatar_mime_type' => $avatarMimeType]);
        }

        $user->load('profile');

        return $this->userHydrator->modelToDto($user);
    }

    public function updateHeader(User $user, ?string $headerPath, ?string $headerMimeType): UserDto
    {
        if (! $user->profile) {
            $user->profile()->create(['header' => $headerPath, 'header_mime_type' => $headerMimeType]);
        } else {
            $user->profile()->update(['header' => $headerPath, 'header_mime_type' => $headerMimeType]);
        }

        $user->load('profile');

        return $this->userHydrator->modelToDto($user);
    }

    public function updateUser(User $user, string $name, ?string $bio, ?string $website = null): UserDto
    {
        $user->update([
            'name' => $name,
        ]);

        $profileData = [
            'bio' => empty($bio) ? null : $bio,
            'website' => empty($website) ? null : $website,
        ];

        if (! $user->profile) {
            $user->profile()->create($profileData);
        } else {
            $user->profile()->update($profileData);
        }

        $user->load('profile');

        return $this->userHydrator->modelToDto($user);
    }

    #[ArrayShape(['followers' => 'int', 'followings' => 'int'])]
    public function getFollowCountsForUser(string $userId): array
    {
        $user = User::where('id', $userId)->first();
        if ($user === null) {
            return [
                'followers' => 0,
                'followings' => 0,
            ];
        }

        return [
            'followers' => $user->followers()->count(),
            'followings' => $user->followings()->count(),
        ];
    }
}
