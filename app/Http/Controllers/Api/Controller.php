<?php

namespace App\Http\Controllers\Api;

use Auth;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '0.1.0-draft',
    description: 'This is a draft version of the Reisetagebuch API documentation. The API is still under development and may change in future releases. Use at your own risk!',
    title: 'Reisetagebuch API Draft',
    license: new OA\License(
        name: 'MIT',
        url: 'https://opensource.org/licenses/MIT'
    ),
    x: [
        'logo' => [
            'url' => 'https://raw.githubusercontent.com/HerrLevin/reisetagebuch/refs/heads/main/public/assets/logo.webp',
        ],
    ]
)]
#[OA\Server(
    url: 'http://localhost/api',
    description: 'Local server'
)]
#[OA\Tag(
    name: 'Posts',
    description: 'Operations related to posts created by users'
)]
class Controller extends \App\Http\Controllers\Controller
{
    public const string OA_DESC_SUCCESS = 'successful operation';

    public const string OA_DESC_NO_CONTENT = 'No Content';

    protected Guard|StatefulGuard $auth;

    public function __construct()
    {
        $this->auth = Auth::guard('api');
    }
}
