<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Backend\UserSettingsBackend;
use App\Http\Requests\SettingsUpdateRequest;
use OpenApi\Attributes as OA;

class UserSettingsController extends Controller
{
    private UserSettingsBackend $backend;

    public function __construct(UserSettingsBackend $userSettingsBackend)
    {
        parent::__construct();
        $this->backend = $userSettingsBackend;
    }

    #[OA\Patch(
        path: '/account/settings',
        operationId: 'updateSettings',
        description: 'Update user settings',
        summary: 'Update settings',
        security: [['oauth2_security_example' => ['write:projects', 'read:projects']]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: SettingsUpdateRequest::class)),
        tags: ['Account'],
        responses: [new OA\Response(response: 204, description: 'No Content')]
    )]
    public function update(SettingsUpdateRequest $request)
    {
        $this->backend->update($request);

        return response()->noContent();
    }
}
