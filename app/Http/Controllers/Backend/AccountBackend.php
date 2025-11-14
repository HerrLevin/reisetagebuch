<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\SettingsUpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

    public function updateSettings(SettingsUpdateRequest $request): void
    {
        /** @var User $user */
        $user = $request->user();

        $user->save();
    }

    public function destroy(Request $request): bool
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        try {

            User::whereId($user->id)->delete();
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return true;
        } catch (Throwable $e) {
            Log::error($e);

            return false;
        }
    }
}
