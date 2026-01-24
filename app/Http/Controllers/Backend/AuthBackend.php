<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Dto\AuthenticatedUserDto;
use App\Dto\UserSettingsDto;
use App\Http\Controllers\Controller;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\StatefulGuard;
use Laravel\Passport\Guards\TokenGuard;
use Laravel\Sanctum\Guard;

class AuthBackend extends Controller
{
    public function getAuthenticatedUser(Guard|StatefulGuard|TokenGuard $guard)
    {
        $user = $guard->user();

        return new AuthenticatedUserDto(
            id: $user->id,
            name: $user->name,
            username: $user->username,
            email: $user->email,
            avatar: $user->profile?->avatar,
            mustVerifyEmail: $user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail(),
            settings: $this->getSettings($user),
            canInviteUsers: config('app.invite.enabled') && $user->can('create', Invite::class),
            traewellingConnected: (bool) $user->traewellingAccount
        );
    }

    private function getSettings(User $user): UserSettingsDto
    {
        return new UserSettingsDto(
            motisRadius: $user->settings->motis_radius,
        );
    }
}
