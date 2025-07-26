<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class TraewellingOAuthController extends Controller
{
    public function redirectToProvider(): \Symfony\Component\HttpFoundation\RedirectResponse|RedirectResponse
    {
        return Socialite::driver('traewelling')->redirect();
    }

    public function handleProviderCallback(Request $request): Redirector|RedirectResponse
    {
        try {
            $user = Socialite::driver('traewelling')->user();
            $authUser = Auth::user();

            SocialAccount::updateOrCreate(
                [
                    'user_id' => $authUser->id,
                    'provider' => 'traewelling',
                ],
                [
                    'provider_user_id' => $user->getId(),
                    'access_token' => $user->token,
                    'refresh_token' => $user->refreshToken ?? null,
                    'token_expires_at' => isset($user->expiresIn) ? now()->addSeconds($user->expiresIn) : null,
                ]
            );

            return redirect(route('account.edit'))->with('success', 'Traewelling account connected!');
        } catch (\Exception $e) {
            Log::error('Traewelling OAuth error: '.$e->getMessage());

            return redirect(route('account.edit'))->with('error', 'Failed to connect Traewelling account.');
        }
    }
}
