<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Backend\AuthBackend;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    private AuthBackend $backend;

    public function __construct(AuthBackend $backend)
    {
        parent::__construct();
        $this->backend = $backend;
    }

    #[OA\Get(
        path: '/auth/user',
        operationId: 'getAuthenticatedUser',
        description: 'Get the currently authenticated user',
        summary: 'Get authenticated user',
        security: [['oauth2_security_example' => ['write:projects', 'read:projects']]],
        tags: ['Authentication'],
        responses: [new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(ref: '#/components/schemas/AuthenticatedUserDto'))]
    )]
    public function user(Request $request)
    {
        $user = $this->backend->getAuthenticatedUser($this->auth);

        return response()->json($user);
    }
}
