<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Backend\AccountBackend;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AccountController extends Controller
{
    private AccountBackend $accountBackend;

    public function __construct(AccountBackend $userSettingsBackend)
    {
        parent::__construct();
        $this->accountBackend = $userSettingsBackend;
    }

    #[OA\Get(
        path: '/account',
        operationId: 'getAccount',
        description: 'Get account details',
        summary: 'Get account',
        security: [['passport' => []]],
        tags: ['Account'],
        responses: [new OA\Response(response: 200, description: 'Account details retrieved successfully', content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'mustVerifyEmail', description: 'Indicates if the user must verify their email address', type: 'boolean', example: true),
                new OA\Property(property: 'traewellingConnected', description: 'Indicates if the user has connected their Traewelling account', type: 'boolean', example: false),
            ]
        )), new OA\Response(response: 401, description: 'Unauthorized')]
    )]
    public function show(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $this->auth->user();

        return response()->json([
            'mustVerifyEmail' => $user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail(),
            'traewellingConnected' => (bool) $user->traewellingAccount,
        ]);
    }

    #[OA\Patch(
        path: '/account',
        operationId: 'updateAccount',
        description: 'Update account details',
        summary: 'Update account',
        security: [['passport' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: ProfileUpdateRequest::class)),
        tags: ['Account'],
        responses: [new OA\Response(response: 204, description: Controller::OA_DESC_NO_CONTENT)]
    )]
    public function update(ProfileUpdateRequest $request)
    {
        $this->accountBackend->update($request);

        return response()->noContent();
    }

    #[OA\Delete(
        path: '/account',
        operationId: 'deleteAccount',
        description: 'Delete the authenticated account',
        summary: 'Delete account',
        security: [['passport' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'password', description: 'Current password of the account', type: 'string', example: 'your-current-password'),
            ]
        )),
        tags: ['Account'],
        responses: [new OA\Response(response: 204, description: Controller::OA_DESC_NO_CONTENT), new OA\Response(response: 405, description: 'Account deletion failed')]
    )]
    public function destroy(Request $request)
    {
        if ($this->accountBackend->destroy($request, $this->auth)) {
            return response()->noContent();
        }

        return response()->json(['message' => 'Account deletion failed'], 405);
    }

    #[OA\Delete(
        path: '/account/socialite/traewelling',
        operationId: 'disconnectTraewelling',
        description: 'Disconnect Traewelling from account',
        summary: 'Disconnect Traewelling',
        security: [['passport' => []]],
        tags: ['Account'],
        responses: [new OA\Response(response: 204, description: Controller::OA_DESC_NO_CONTENT), new OA\Response(response: 405, description: 'Disconnection failed')]
    )]
    public function disconnectTraewelling(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $this->auth->user();

        if ($this->accountBackend->disconnectTraewelling($user)) {
            return response()->noContent();
        }

        return response()->json(['message' => 'Traewelling account disconnection failed'], 405);
    }
}
