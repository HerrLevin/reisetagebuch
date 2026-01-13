<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Requests\BasePostRequest;
use App\Http\Requests\FilterPostsRequest;
use App\Http\Requests\LocationBasePostRequest;
use App\Http\Requests\MassEditPostRequest;
use App\Http\Requests\TransportBasePostCreateRequest;
use App\Http\Requests\TransportPostUpdateRequest;
use App\Http\Requests\TransportTimesUpdateRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Inertia\ResponseFactory;

class PostController extends Controller
{
    private \App\Http\Controllers\Backend\PostController $postController;

    public function __construct(\App\Http\Controllers\Backend\PostController $postController)
    {
        $this->postController = $postController;
    }

    public function create(): Response|ResponseFactory
    {
        return Inertia::render('NewPostDialog/CreateLocationPost');
    }

    /**
     * @throws AuthorizationException
     */
    public function show(string $postId): Response|ResponseFactory
    {
        return Inertia::render('SinglePost', [
            'post' => $this->postController->show($postId),
        ]);
    }

    public function createTransport(): Response|ResponseFactory
    {
        return Inertia::render('NewPostDialog/CreateTransportPost');
    }

    public function createText(): Response|ResponseFactory
    {
        return Inertia::render('NewPostDialog/CreateTextPost');
    }

    public function storeTransport(TransportBasePostCreateRequest $request): RedirectResponse
    {
        $post = $this->postController->storeMotisTransport($request);

        return to_route('posts.show', [
            'postId' => $post->id,
        ]);
    }

    public function storeText(BasePostRequest $request): RedirectResponse
    {
        $post = $this->postController->storeText($request);

        return to_route('posts.show', [
            'postId' => $post->id,
        ]);
    }

    public function storeLocation(LocationBasePostRequest $request): RedirectResponse
    {
        $post = $this->postController->storeLocation($request);

        return to_route('posts.show', [
            'postId' => $post->id,
        ]);

    }

    public function dashboard(): Response|ResponseFactory
    {
        return Inertia::render('Dashboard');
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(string $postId): RedirectResponse
    {
        $this->postController->destroy($postId);

        return to_route('dashboard');
    }

    /**
     * @throws AuthorizationException
     */
    public function edit(string $postId): Response|ResponseFactory
    {
        $post = $this->postController->edit($postId);

        return Inertia::render('EditPost', [
            'post' => $post,
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(string $postId, BasePostRequest $request): RedirectResponse
    {
        $post = $this->postController->updatePost($postId, $request);

        return to_route('posts.show', [
            'postId' => $post->id,
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function editTransport(string $postId): Response|ResponseFactory
    {
        $trip = $this->postController->editTransport($postId);

        return inertia('NewPostDialog/ListStopovers', [
            'trip' => $trip,
            'startTime' => $trip->startTime,
            'postId' => $postId,
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function editTimesTransport(string $postId): Response|ResponseFactory
    {
        $post = $this->postController->editTimesTransport($postId);

        return inertia('NewPostDialog/EditTransportTimes', [
            'post' => $post,
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function updateTimesTransport(string $postId, TransportTimesUpdateRequest $request): RedirectResponse
    {
        $post = $this->postController->updateTimesTransport($postId, $request);

        return to_route('posts.show', [
            'postId' => $post->id,
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function updateTransport(string $postId, TransportPostUpdateRequest $request): RedirectResponse
    {
        $post = $this->postController->updateTransport($postId, $request);

        return to_route('posts.show', [
            'postId' => $post->id,
        ]);
    }

    public function filter(FilterPostsRequest $request): Response|ResponseFactory
    {
        $user = Auth::user();
        $posts = $this->postController->filter($request);

        $userTags = $user->hashTags()->orderBy('relevance', 'desc')->pluck('value')->toArray();

        return Inertia::render('Posts/Filter', [
            'posts' => $posts->previousCursor ? Inertia::merge($posts->items) : $posts->items,
            'nextCursor' => $posts->nextCursor,
            'previousCursor' => $posts->previousCursor,
            'filters' => [
                'dateFrom' => $request->input('dateFrom'),
                'dateTo' => $request->input('dateTo'),
                'visibility' => $request->input('visibility', []),
                'travelReason' => $request->input('travelReason', []),
                'tags' => $request->input('tags', []),
            ],
            'availableTags' => $userTags,
        ]);
    }

    public function massEdit(MassEditPostRequest $request): RedirectResponse
    {
        $this->postController->massEdit($request);

        return to_route('posts.filter');
    }
}
