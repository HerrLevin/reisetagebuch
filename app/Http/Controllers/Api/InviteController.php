<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Backend\InviteController as Backend;
use App\Http\Requests\StoreInviteCodeRequest;
use App\Http\Resources\InviteDto;
use App\Models\Invite;
use OpenApi\Attributes as OA;

class InviteController extends Controller
{
    private Backend $backend;

    public function __construct(Backend $backend)
    {
        $this->backend = $backend;
        parent::__construct();
    }

    #[OA\Get(
        path: '/invites',
        operationId: 'listInvites',
        description: 'List invite codes for the authenticated user',
        summary: 'List invites',
        security: [
            [
                'oauth2_security_example' => ['write:projects', 'read:projects'],
            ],
        ],
        tags: ['Invites'],
        responses: [
            new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: InviteDto::class))),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function index(): array
    {
        $this->authorize('create', Invite::class);

        /** @var \App\Models\User $user */
        $user = $this->auth->user();

        return $this->backend->index($user->id);
    }

    #[OA\Post(
        path: '/invites',
        operationId: 'createInvite',
        description: 'Create a new invite code',
        summary: 'Create invite',
        security: [
            [
                'oauth2_security_example' => ['write:projects', 'read:projects'],
            ],
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: StoreInviteCodeRequest::class)),
        tags: ['Invites'],
        responses: [
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function store(StoreInviteCodeRequest $request): array
    {
        $this->authorize('create', Invite::class);

        /** @var \App\Models\User $user */
        $user = $this->auth->user();

        $this->backend->store($user->id, $request->input('expires_at'));

        return ['success' => true];
    }

    #[OA\Delete(
        path: '/invites/{inviteCode}',
        operationId: 'deleteInvite',
        description: 'Delete an invite code',
        summary: 'Delete invite',
        security: [
            [
                'oauth2_security_example' => ['write:projects', 'read:projects'],
            ],
        ],
        tags: ['Invites'],
        parameters: [
            new OA\Parameter(name: 'inviteCode', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 204, description: Controller::OA_DESC_NO_CONTENT),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function destroy(string $inviteCode): array
    {
        $this->backend->destroy($inviteCode);

        return ['success' => true];
    }
}
