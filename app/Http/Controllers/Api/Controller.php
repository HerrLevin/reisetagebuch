<?php

namespace App\Http\Controllers\Api;

use Auth;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;

class Controller extends \App\Http\Controllers\Controller
{
    protected Guard|StatefulGuard $auth;

    public function __construct()
    {
        $this->auth = Auth::guard('api');
    }
}
