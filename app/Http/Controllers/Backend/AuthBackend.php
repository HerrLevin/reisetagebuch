<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Dto\AuthenticatedUserDto;
use App\Dto\UserSettingsDto;
use App\Http\Controllers\Controller;
use App\Models\Invite;
use App\Models\User;
use App\Repositories\PrivacyPolicyRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Guards\TokenGuard;
use Throwable;

class AuthBackend extends Controller
{
    public function __construct(
        private readonly PrivacyPolicyRepository $privacyPolicyRepository,
        private readonly UrlGenerator $urlGenerator
    ) {}

    public function getAuthenticatedUser(StatefulGuard|TokenGuard|User $guardOrUser)
    {
        $user = $guardOrUser instanceof User ? $guardOrUser : $guardOrUser->user();

        return new AuthenticatedUserDto(
            id: $user->id,
            name: $user->name,
            username: $user->username,
            email: $user->email,
            avatar: $user->profile?->avatar ? $this->urlGenerator->to('/files/'.$user->profile?->avatar) : null,
            mustVerifyEmail: $user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail(),
            settings: $this->getSettings($user),
            canInviteUsers: config('app.invite.enabled') && $user->can('create', Invite::class),
            traewellingConnected: (bool) $user->traewellingAccount,
            isAdmin: $user->isAdmin(),
            hasAcceptedCurrentPrivacyPolicy: $this->hasAcceptedCurrentPrivacyPolicy($user),
        );
    }

    /**
     * @param  array{name: string, username: string, email: string, password: string}  $data
     *
     * @throws Throwable
     */
    public function registerUser(array $data, ?Invite $invite = null): User
    {
        $user = DB::transaction(function () use ($data, $invite) {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            $user->profile()->create();
            $user->settings()->create();
            $user->statistics()->create();
            $invite?->update([
                'used_by' => $user->id,
                'used_at' => now(),
            ]);

            return $user;
        });

        event(new Registered($user));

        return $user;
    }

    private function hasAcceptedCurrentPrivacyPolicy(User $user): bool
    {
        $current = $this->privacyPolicyRepository->currentModel();

        return ! $current || $this->privacyPolicyRepository->hasAccepted($user, $current);
    }

    private function getSettings(User $user): UserSettingsDto
    {
        return new UserSettingsDto(
            motisRadius: $user->settings?->motis_radius,
            requiresFollowRequest: $user->settings?->requires_follow_request ?? false,
            hidePostsAfter: ! empty($user->settings?->hide_posts_after) ? (float) $user->settings->hide_posts_after : null,
        );
    }
}
