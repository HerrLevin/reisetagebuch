<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Http\Resources\UserDto;
use App\Hydrators\UserHydrator;
use App\Models\User;

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

    public function updateUser(User $user, string $name, ?string $bio, ?string $website, ?string $avatarPath, ?string $headerPath): UserDto
    {
        $user->update([
            'name' => $name,
        ]);

        $profileData = [
            'bio' => empty($bio) ? null : $bio,
            'website' => empty($website) ? null : $website,
            'header' => $headerPath,
            'avatar' => $avatarPath,
        ];

        if (! $user->profile) {
            $user->profile()->create($profileData);
        } else {
            $user->profile()->update($profileData);
        }

        $user->load('profile');

        return $this->userHydrator->modelToDto($user);
    }
}
