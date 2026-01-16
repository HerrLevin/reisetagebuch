<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Backend\UserSettingsBackend;
use App\Http\Requests\SettingsUpdateRequest;

class UserSettingsController extends Controller
{
    private UserSettingsBackend $backend;

    public function __construct(UserSettingsBackend $userSettingsBackend)
    {
        parent::__construct();
        $this->backend = $userSettingsBackend;
    }

    public function update(SettingsUpdateRequest $request)
    {
        $this->backend->update($request);

        return response()->noContent();
    }
}
