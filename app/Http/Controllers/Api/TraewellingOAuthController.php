<?php

namespace App\Http\Controllers\Api;

use App\Dto\ErrorDto;
use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use OpenApi\Attributes as OA;

class TraewellingOAuthController extends Controller
{
    #[OA\Get(
        path: '/socialite/traewelling/connect',
        operationId: 'connectTraewelling',
        summary: 'Redirects the user to Traewelling for OAuth authentication',
        security: [['passport' => []]],
        tags: ['Socialite'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Redirect to Traewelling for authentication',
                content: new OA\JsonContent(
                    required: ['url'],
                    properties: [
                        new OA\Property(
                            property: 'url',
                            description: 'The URL to redirect the user to for Traewelling authentication',
                            type: 'string'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized - user must be authenticated to connect Traewelling account'
            ),
        ]
    )]
    public function redirectToProvider(): JsonResponse
    {
        $url = Socialite::driver('traewelling')->stateless()->redirect()->getTargetUrl();

        return response()->json([
            'url' => $url,
        ]);
    }

    #[OA\Get(
        path: '/socialite/traewelling/callback',
        operationId: 'handleTraewellingCallback',
        summary: 'Handles the callback from Traewelling after OAuth authentication',
        security: [['passport' => []]],
        tags: ['Socialite'],
        parameters: [
            new OA\Parameter(
                name: 'code',
                description: 'The authorization code returned by Traewelling after successful authentication',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'state',
                description: 'The state parameter to prevent CSRF attacks (optional)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Traewelling account connected successfully'
            ),
            new OA\Response(
                response: 500,
                description: 'Internal Server Error - failed to connect Traewelling account'
            ),
        ]
    )]
    public function handleProviderCallback()
    {
        try {
            $user = Socialite::driver('traewelling')->stateless()->user();
            $authUser = Auth::user();

            SocialAccount::updateOrCreate(
                [
                    'user_id' => $authUser->id,
                    'provider' => 'traewelling',
                ],
                [
                    'provider_user_id' => $user->getId(),
                    'access_token' => $user->token,
                    'refresh_token' => $user->refreshToken ?? null,
                    'token_expires_at' => isset($user->expiresIn) ? now()->addSeconds($user->expiresIn) : null,
                ]
            );

            return response(null, 204);
        } catch (\Exception $e) {
            Log::error('Traewelling OAuth error: '.$e->getMessage());

            return response(new ErrorDto($e->getMessage()), 500);
        }
    }
}
