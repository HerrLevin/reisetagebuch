<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Backend\AccountBackend;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    private AccountBackend $accountBackend;

    public function __construct(AccountBackend $userSettingsBackend)
    {
        parent::__construct();
        $this->accountBackend = $userSettingsBackend;
    }

    public function update(ProfileUpdateRequest $request)
    {
        $this->accountBackend->update($request);

        return response()->noContent();
    }

    public function destroy(Request $request)
    {
        if ($this->accountBackend->destroy($request, $this->auth)) {
            return response()->noContent();
        }

        return response()->json(['message' => 'Account deletion failed'], 405);
    }

    public function disconnectTraewelling(Request $request)
    {
        if ($this->accountBackend->disconnectTraewelling($this->auth->user())) {
            return response()->noContent();
        }

        return response()->json(['message' => 'Traewelling account disconnection failed'], 405);
    }
}
