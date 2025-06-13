<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Backend\UserSettingsBackend;
use App\Http\Controllers\Controller;
use App\Http\Requests\SettingsUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class UserSettingsController extends Controller
{
    private UserSettingsBackend $backend;

    public function __construct(UserSettingsBackend $userSettingsBackend)
    {
        $this->backend = $userSettingsBackend;
    }

    public function update(SettingsUpdateRequest $request): RedirectResponse
    {
        $this->backend->update($request);

        return Redirect::route('account.edit');
    }
}
