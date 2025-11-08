<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Backend\AccountBackend;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\SettingsUpdateRequest;
use App\Models\SocialAccount;
use App\Services\Socialite\TraewellingUser;
use Exception;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\Token;
use Log;

class AccountController extends Controller
{
    private AccountBackend $accountBackend;

    public function __construct(AccountBackend $accountController)
    {
        $this->accountBackend = $accountController;
    }

    public function edit(Request $request): Response
    {
        return Inertia::render('Settings/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'traewellingConnected' => (bool) $request->user()->traewellingAccount,
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $this->accountBackend->update($request);

        return Redirect::route('account.edit');
    }

    public function updateSettings(SettingsUpdateRequest $request): RedirectResponse
    {
        $this->accountBackend->updateSettings($request);

        return Redirect::route('account.edit');
    }

    public function destroy(Request $request): RedirectResponse
    {
        if ($this->accountBackend->destroy($request)) {
            return Redirect::to('/');
        }

        return Redirect::route('account.edit')->with('error', 'errors.failed_delete_account');
    }

    public function disconnectTraewelling(Request $request): RedirectResponse
    {
        /** @var SocialAccount|null $account */
        $account = $request->user()->traewellingAccount;
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
            } catch (Exception $e) {
                // Log the error and continue with account deletion
                Log::error('Traewelling logout error: '.$e->getMessage());
            }

            $account->delete();
        }

        return Redirect::route('account.edit')->with('success', 'Traewelling account disconnected.');
    }
}
