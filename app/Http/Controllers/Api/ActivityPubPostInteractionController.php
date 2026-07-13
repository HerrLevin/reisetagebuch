<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Backend\ActivityPubPostInteractionBackend;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class ActivityPubPostInteractionController extends Controller
{
    public function __construct(
        private readonly ActivityPubPostInteractionBackend $backend,
    ) {
        parent::__construct();
    }

    #[OA\Post(
        path: '/activitypub/posts/{postId}/likes',
        operationId: 'likeApPost',
        summary: 'Like an ActivityPub post',
        security: [['passport' => []]],
        tags: ['ActivityPub'],
        parameters: [
            new OA\Parameter(name: 'postId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Like toggled'),
            new OA\Response(response: 404, description: 'Post not found'),
        ]
    )]
    public function like(string $postId): JsonResponse
    {
        $result = $this->backend->like($this->auth->user()->id, $postId);

        return response()->json($result);
    }

    #[OA\Delete(
        path: '/activitypub/posts/{postId}/likes',
        operationId: 'unlikeApPost',
        summary: 'Unlike an ActivityPub post',
        security: [['passport' => []]],
        tags: ['ActivityPub'],
        parameters: [
            new OA\Parameter(name: 'postId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Like removed'),
            new OA\Response(response: 404, description: 'Post not found'),
        ]
    )]
    public function unlike(string $postId): JsonResponse
    {
        $result = $this->backend->unlike($this->auth->user()->id, $postId);

        return response()->json($result);
    }
}
