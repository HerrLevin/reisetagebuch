<?php

namespace App\Http\Controllers\Api;

use App\Dto\LikeDto;
use App\Http\Controllers\Backend\LikeController as Backend;
use App\Models\Post;
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

    #[OA\Post(
        path: '/posts/{post}/likes',
        operationId: 'likePost',
        description: 'Like a post',
        summary: 'Like post',
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(name: 'post', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(ref: LikeDto::class)),
            new OA\Response(response: 404, description: 'Post not found'),
        ]
    )]
    public function store(Request $request, Post $post): LikeDto
    {
        return $this->likeController->store($request->user(), $post);
    }

    #[OA\Delete(
        path: '/posts/{post}/likes',
        operationId: 'unlikePost',
        description: 'Remove like from a post',
        summary: 'Unlike post',
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(name: 'post', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(ref: LikeDto::class)),
            new OA\Response(response: 404, description: 'Post not found'),
        ]
    )]
    public function destroy(Request $request, Post $post)
    {
        return $this->likeController->destroy($request->user(), $post);
    }
}
