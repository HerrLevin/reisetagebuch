<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingsUpdateRequest;
use App\Models\User;
use App\Models\UserSettings;

class UserSettingsBackend extends Controller
{
    public function update(SettingsUpdateRequest $request): void
    {
        /** @var User $user */
        $user = $request->user()->load('settings');
        $settings = $user->settings;

        if ($settings === null) {
            $settings = new UserSettings;
            $settings->user_id = $user->id;
            $user->settings()->save($settings);
        }

        $settings->motis_radius = $request->motisRadius;

        $settings->save();
    }
}
