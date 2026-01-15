<?php

namespace App\Http\Controllers\Api;

use App\Dto\PostPaginationDto;
use App\Http\Requests\BasePostRequest;
use App\Http\Requests\FilterPostsRequest;
use App\Http\Requests\LocationBasePostRequest;
use App\Http\Requests\MassEditPostRequest;
use App\Http\Requests\TransportBasePostCreateRequest;
use App\Http\Requests\TransportPostUpdateRequest;
use App\Http\Requests\TransportTimesUpdateRequest;
use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    private \App\Http\Controllers\Backend\PostController $postController;

    public function __construct(\App\Http\Controllers\Backend\PostController $postController)
    {
        $this->postController = $postController;
        parent::__construct();
    }

    public function timeline(): PostPaginationDto
    {
        return $this->postController->dashboard($this->auth->user());
    }

    public function postsForUsername(string $username, Request $request): PostPaginationDto
    {
        return $this->postController->postsForUser($username, $this->auth->user());
    }

    public function show(string $postId): BasePost|TransportPost|LocationPost
    {
        return $this->postController->show($postId);
    }

    public function filter(FilterPostsRequest $request): array
    {
        $posts = $this->postController->filter($request);
        $userTags = Auth::user()->hashTags()->orderBy('relevance', 'desc')->pluck('value')->toArray();

        return [
            'items' => $posts->items,
            'nextCursor' => $posts->nextCursor,
            'previousCursor' => $posts->previousCursor,
            'availableTags' => $userTags,
        ];
    }

    public function massEdit(MassEditPostRequest $request): array
    {
        return $this->postController->massEdit($request);
    }

    public function destroy(string $postId): Response
    {
        $this->postController->destroy($postId);

        return response()->noContent();
    }

    public function storeTransport(TransportBasePostCreateRequest $request): TransportPost
    {
        return $this->postController->storeMotisTransport($request);
    }

    public function storeText(BasePostRequest $request): BasePost
    {
        return $this->postController->storeText($request);
    }

    public function storeLocation(LocationBasePostRequest $request): LocationPost
    {
        return $this->postController->storeLocation($request);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(string $postId, BasePostRequest $request): BasePost|TransportPost|LocationPost
    {
        return $this->postController->updatePost($postId, $request);
    }

    /**
     * @throws AuthorizationException
     */
    public function updateTransport(string $postId, TransportPostUpdateRequest $request): TransportPost
    {
        return $this->postController->updateTransport($postId, $request);
    }

    /**
     * @throws AuthorizationException
     */
    public function updateTimesTransport(string $postId, TransportTimesUpdateRequest $request): TransportPost
    {
        return $this->postController->updateTimesTransport($postId, $request);
    }
}
