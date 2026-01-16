<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Services\Socialite\TraewellingUser;
use Exception;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\Token;
use Throwable;

class AccountBackend extends Controller
{
    public function update(ProfileUpdateRequest $request): void
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $user->save();
    }

    public function destroy(Request $request, StatefulGuard|Guard $guard): bool
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        try {
            User::whereId($guard->user()->id)->delete();

            return true;
        } catch (Throwable $e) {
            Log::error($e);

            return false;
        }
    }

    public function disconnectTraewelling(User $user): bool
    {
        $account = $user->traewellingAccount;
        if ($account) {
            try {
                $socialite = Socialite::driver('traewelling');
                $accessToken = $account->access_token;

                if ($account->token_expires_at?->isPast()) {
                    // If the token has expired, we need to refresh it
                    /** @var Token $token */
                    $token = $socialite->refreshToken($account->refresh_token);
                    $accessToken = $token->token;
                }

                /**
                 * @var TraewellingUser $user
                 */
                $user = Socialite::driver('traewelling')->userFromToken($accessToken);
                $user->logout();
                $account->delete();

                return true;
            } catch (Exception $e) {
                // Log the error and continue with account deletion
                Log::error('Traewelling logout error: '.$e->getMessage());
            }
        }

        return false;
    }
}
