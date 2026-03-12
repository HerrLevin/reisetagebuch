<?php

namespace App\Http\Controllers\Api;

use App\Dto\LikeDto;
use App\Http\Controllers\Backend\LikeController as Backend;
use App\Http\Resources\UserDto;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class LikeController extends Controller
{
    private Backend $likeController;

    public function __construct(Backend $likeController)
    {
        parent::__construct();
        $this->likeController = $likeController;
    }

    #[OA\Get(
        path: '/posts/{postId}/likes',
        operationId: 'getPostLikes',
        description: 'Get users who liked a post',
        summary: 'Get post likes',
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(name: 'postId', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: UserDto::class))),
            new OA\Response(response: 404, description: 'Post not found'),
        ]
    )]
    public function index(string $postId): array
    {
        return $this->likeController->index($postId);
    }

    #[OA\Post(
        path: '/posts/{postId}/likes',
        operationId: 'likePost',
        description: 'Like a post',
        summary: 'Like post',
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(name: 'postId', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(ref: LikeDto::class)),
            new OA\Response(response: 404, description: 'Post not found'),
        ]
    )]
    public function store(Request $request, string $postId): LikeDto
    {
        return $this->likeController->store($request->user(), $postId);
    }

    #[OA\Delete(
        path: '/posts/{postId}/likes',
        operationId: 'unlikePost',
        description: 'Remove like from a post',
        summary: 'Unlike post',
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(name: 'postId', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(ref: LikeDto::class)),
            new OA\Response(response: 404, description: 'Post not found'),
        ]
    )]
    public function destroy(Request $request, string $postId)
    {
        return $this->likeController->destroy($request->user(), $postId);
    }
}
