<?php

namespace App\Http\Controllers\Api;

use App\Dto\FilteredPostPaginationDto;
use App\Dto\PostPaginationDto;
use App\Enums\PostMetaInfo\TravelReason;
use App\Enums\Visibility;
use App\Exceptions\NegativePeriodException;
use App\Http\Requests\BasePostRequest;
use App\Http\Requests\FilterPostsRequest;
use App\Http\Requests\LocationBasePostRequest;
use App\Http\Requests\MassEditPostRequest;
use App\Http\Requests\TransportBasePostCreateRequest;
use App\Http\Requests\TransportPostExitUpdateRequest;
use App\Http\Requests\TransportTimesUpdateRequest;
use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class PostController extends Controller
{
    private \App\Http\Controllers\Backend\PostController $postController;

    public function __construct(\App\Http\Controllers\Backend\PostController $postController)
    {
        $this->postController = $postController;
        parent::__construct();
    }

    #[OA\Get(
        path: '/timeline',
        operationId: 'timeline',
        description: 'Returns paginated posts for the authenticated user timeline',
        summary: 'Get timeline posts',
        security: [
            [
                'oauth2_security_example' => ['write:projects', 'read:projects'],
            ],
        ],
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(
                name: 'cursor',
                description: 'Pagination cursor',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(ref: PostPaginationDto::class)
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 404, description: 'Resource Not Found'),
        ]
    )]
    public function timeline(): PostPaginationDto
    {
        return $this->postController->dashboard($this->auth->user());
    }

    #[OA\Get(
        path: '/users/{userId}/posts',
        operationId: 'postsForUser',
        description: 'Returns paginated posts for a specific user',
        summary: 'Get posts for a specific user',
        security: [
            [
                'oauth2_security_example' => ['write:projects', 'read:projects'],
            ],
        ],
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(
                name: 'userId',
                description: 'User id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
            new OA\Parameter(
                name: 'cursor',
                description: 'Pagination cursor',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(ref: PostPaginationDto::class)
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 404, description: 'Resource Not Found'),
        ]
    )]
    public function postsForUser(string $userId): PostPaginationDto
    {
        return $this->postController->postsForUser($userId, $this->auth->user());
    }

    #[OA\Get(
        path: '/posts/{id}',
        operationId: 'showPost',
        description: 'Returns post data',
        summary: 'Get post by ID',
        security: [
            [
                'oauth2_security_example' => ['write:projects', 'read:projects'],
            ],
        ],
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Post id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    oneOf: [
                        new OA\Schema(ref: BasePost::class),
                        new OA\Schema(ref: TransportPost::class),
                        new OA\Schema(ref: LocationPost::class),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 404, description: 'Resource Not Found'),
        ]
    )]
    public function show(string $postId): BasePost|TransportPost|LocationPost
    {
        return $this->postController->show($postId);
    }

    #[OA\Get(
        path: '/posts',
        operationId: 'filterPosts',
        description: 'Returns filtered posts',
        summary: 'Filter posts',
        security: [
            [
                'oauth2_security_example' => ['write:projects', 'read:projects'],
            ],
        ],
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(
                name: 'cursor',
                description: 'Pagination cursor',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'dateFrom',
                description: 'Filter posts from this date (YYYY-MM-DD)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'dateTo',
                description: 'Filter posts to this date (YYYY-MM-DD)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'visibility',
                description: 'Filter by visibility (e.g., PUBLIC, PRIVATE)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'array', items: new OA\Items(ref: Visibility::class))
            ),
            new OA\Parameter(
                name: 'travelReason',
                description: 'Filter by travel reason (e.g., WORK, LEISURE)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'array', items: new OA\Items(ref: TravelReason::class))
            ),
            new OA\Parameter(
                name: 'tags',
                description: 'Filter by tags',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string'))
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(ref: FilteredPostPaginationDto::class)
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 404, description: 'Resource Not Found'),
        ]
    )]
    public function index(FilterPostsRequest $request): FilteredPostPaginationDto
    {
        $posts = $this->postController->filter($request, $this->auth->user());
        $userTags = $this->auth->user()->hashTags()->orderBy('relevance', 'desc')->pluck('value')->toArray();

        return new FilteredPostPaginationDto(
            perPage: $posts->perPage,
            nextCursor: $posts->nextCursor,
            previousCursor: $posts->previousCursor,
            items: $posts->items,
            availableTags: $userTags,
        );
    }

    #[OA\Post(
        path: '/posts/mass-edit',
        operationId: 'massEditPosts',
        description: 'Mass edit multiple posts',
        summary: 'Mass edit posts',
        security: [
            [
                'oauth2_security_example' => ['write:projects', 'read:projects'],
            ],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: MassEditPostRequest::class)
        ),
        tags: ['Posts'],
        responses: [
            new OA\Response(response: 200, description: 'successful operation', content: new OA\JsonContent(type: 'array', items: new OA\Items(type: 'string'))),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function massEdit(MassEditPostRequest $request): array
    {
        return $this->postController->massEdit($request);
    }

    #[OA\Delete(
        path: '/posts/{id}',
        operationId: 'deletePost',
        description: 'Delete a post by id',
        summary: 'Delete post',
        security: [
            [
                'oauth2_security_example' => ['write:projects', 'read:projects'],
            ],
        ],
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Post id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(response: 204, description: 'No Content'),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Resource Not Found'),
        ]
    )]
    public function destroy(string $postId): Response
    {
        $this->postController->destroy($postId);

        return response()->noContent();
    }

    #[OA\Post(
        path: '/posts/transport',
        operationId: 'storeTransportPost',
        description: 'Create a transport post',
        summary: 'Create transport post',
        security: [
            [
                'oauth2_security_example' => ['write:projects', 'read:projects'],
            ],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: TransportBasePostCreateRequest::class)
        ),
        tags: ['Posts'],
        responses: [
            new OA\Response(response: 201, description: 'Created', content: new OA\JsonContent(ref: TransportPost::class)),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function storeTransport(TransportBasePostCreateRequest $request): TransportPost
    {
        return $this->postController->storeMotisTransport($request);
    }

    #[OA\Post(
        path: '/posts/text',
        operationId: 'storeTextPost',
        description: 'Create a text post',
        summary: 'Create text post',
        security: [
            [
                'oauth2_security_example' => ['write:projects', 'read:projects'],
            ],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: BasePostRequest::class)
        ),
        tags: ['Posts'],
        responses: [
            new OA\Response(response: 201, description: 'Created', content: new OA\JsonContent(ref: BasePost::class)),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function storeText(BasePostRequest $request): BasePost
    {
        return $this->postController->storeText($request);
    }

    #[OA\Post(
        path: '/posts/location',
        operationId: 'storeLocationPost',
        description: 'Create a location post',
        summary: 'Create location post',
        security: [
            [
                'oauth2_security_example' => ['write:projects', 'read:projects'],
            ],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: LocationBasePostRequest::class)
        ),
        tags: ['Posts'],
        responses: [
            new OA\Response(response: 201, description: 'Created', content: new OA\JsonContent(ref: LocationPost::class)),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function storeLocation(LocationBasePostRequest $request): LocationPost
    {
        return $this->postController->storeLocation($request);
    }

    /**
     * @throws AuthorizationException
     */
    #[OA\Put(
        path: '/posts/{id}',
        operationId: 'updatePost',
        description: 'Update a post (text, transport or location)',
        summary: 'Update post',
        security: [
            [
                'oauth2_security_example' => ['write:projects', 'read:projects'],
            ],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: BasePostRequest::class)
        ),
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Post id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'successful operation',
                content: new OA\JsonContent(
                    oneOf: [
                        new OA\Schema(ref: BasePost::class),
                        new OA\Schema(ref: TransportPost::class),
                        new OA\Schema(ref: LocationPost::class),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Resource Not Found'),
        ]
    )]
    public function update(string $postId, BasePostRequest $request): BasePost|TransportPost|LocationPost
    {
        return $this->postController->updatePost($postId, $request);
    }

    /**
     * @throws AuthorizationException
     */
    #[OA\Put(
        path: '/posts/{id}/transport/exit',
        operationId: 'updateTransportPostExit',
        description: 'Update transport-specific fields of a post',
        summary: 'Update transport post',
        security: [
            [
                'oauth2_security_example' => ['write:projects', 'read:projects'],
            ],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: TransportPostExitUpdateRequest::class)
        ),
        tags: ['Posts', 'TransportPosts'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Post id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'successful operation', content: new OA\JsonContent(ref: TransportPost::class)),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Resource Not Found'),
        ]
    )]
    public function updateTransportPostExit(string $postId, TransportPostExitUpdateRequest $request): TransportPost
    {
        return $this->postController->updateTransportPostExit($postId, $request);
    }

    /**
     * @throws AuthorizationException
     */
    #[OA\Put(
        path: '/posts/{id}/transport/times',
        operationId: 'updateTransportTimes',
        description: 'Update transport times for a transport post',
        summary: 'Update transport times',
        security: [
            [
                'oauth2_security_example' => ['write:projects', 'read:projects'],
            ],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: TransportTimesUpdateRequest::class)
        ),
        tags: ['Posts', 'TransportPosts'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Post id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'successful operation', content: new OA\JsonContent(ref: TransportPost::class)),
            new OA\Response(response: 400, description: 'Bad request'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Resource Not Found'),
        ]
    )]
    public function updateTimesTransport(string $postId, TransportTimesUpdateRequest $request): TransportPost
    {
        try {
            return $this->postController->updateTimesTransport($postId, $request);
        } catch (NegativePeriodException $exception) {
            abort(400, $exception->getMessage());
        }
    }
}
