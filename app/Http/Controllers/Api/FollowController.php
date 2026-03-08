<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ConflictException;
use App\Exceptions\InsufficientRightsException;
use App\Http\Resources\UserDto;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class FollowController extends Controller
{
    private \App\Http\Controllers\Backend\FollowController $backend;

    public function __construct(\App\Http\Controllers\Backend\FollowController $backend)
    {
        parent::__construct();
        $this->backend = $backend;
    }

    #[OA\Get(
        path: '/users/{userId}/followers',
        operationId: 'getFollowers',
        description: 'Return the users, that follow a user',
        summary: 'Get followers data',
        tags: ['Follows'],
        parameters: [new OA\Parameter(name: 'userId', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(
                response: 200,
                description: Controller::OA_DESC_SUCCESS,
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: UserDto::class)
                )
            ),
        ]
    )]
    public function getFollowers(string $userId): array
    {
        return $this->backend->getFollowers($userId);
    }

    #[OA\Get(
        path: '/users/{userId}/followings',
        operationId: 'getFollowings',
        description: 'Return the users, that a user follows',
        summary: 'Get followings data',
        tags: ['Follows'],
        parameters: [new OA\Parameter(name: 'userId', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(
                response: 200,
                description: Controller::OA_DESC_SUCCESS,
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: UserDto::class)
                )
            ),
        ]
    )]
    public function getFollowings(string $userId): array
    {
        return $this->backend->getFollowings($userId);
    }

    #[OA\Post(
        path: '/users/{userId}/followers/{targetId}',
        operationId: 'createFollow',
        description: 'Create a follow relationship between two users',
        summary: 'Create follow relationship',
        tags: ['Follows'],
        parameters: [
            new OA\Parameter(name: 'userId', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'targetId', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: Controller::OA_DESC_SUCCESS
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request, e.g. when trying to follow yourself'
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden, e.g. when trying to follow on behalf of another user'
            ),
            new OA\Response(
                response: 404,
                description: 'Not found, e.g. when the user or target user does not exist'
            ),
        ]
    )]
    public function createFollow(string $userId, string $targetId): ResponseFactory|Response
    {
        try {
            $this->backend->createFollow($userId, $targetId, $this->auth->user());

            return response(null, 204);
        } catch (ConflictException $e) {
            abort(400, $e->getMessage());
        } catch (InsufficientRightsException $e) {
            abort(403, $e->getMessage());
        }
    }

    #[OA\Delete(
        path: '/users/{userId}/followers/{targetId}',
        operationId: 'deleteFollow',
        description: 'Delete a follow relationship between two users',
        summary: 'Delete follow relationship',
        tags: ['Follows'],
        parameters: [
            new OA\Parameter(name: 'userId', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'targetId', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: Controller::OA_DESC_SUCCESS
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden, e.g. when trying to follow on behalf of another user'
            ),
            new OA\Response(
                response: 404,
                description: 'Not found, e.g. when the user or target user does not exist'
            ),
        ]
    )]
    public function deleteFollow(string $userId, string $targetId): ResponseFactory|Response
    {
        try {
            $this->backend->deleteFollow($userId, $targetId, $this->auth->user());
        } catch (InsufficientRightsException $e) {
            abort(403, $e->getMessage());
        }

        return response(null, 204);
    }
}
