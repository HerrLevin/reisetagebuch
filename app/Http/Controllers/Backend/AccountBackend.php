<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\SettingsUpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function updateSettings(SettingsUpdateRequest $request): void
    {
        /** @var User $user */
        $user = $request->user();

        if (!empty($request->defaultNewPostView)) {
            $user->default_new_post_view = $request->defaultNewPostView;
        }

        $user->save();
    }

    public function destroy(Request $request): void
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        /** @var User $user */
        $user->delete();
        $user->posts()->delete();
        $user->profile()->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
