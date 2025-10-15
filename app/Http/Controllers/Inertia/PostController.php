<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Requests\LocationPostRequest;
use App\Http\Requests\PostRequest;
use App\Http\Requests\TransportPostCreateRequest;
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

    public function storeTransport(TransportPostCreateRequest $request): RedirectResponse
    {
        $post = $this->postController->storeMotisTransport($request);

        return to_route('posts.show', [
            'postId' => $post->id,
        ]);
    }

    public function storeText(PostRequest $request): RedirectResponse
    {
        $post = $this->postController->storeText($request);

        return to_route('posts.show', [
            'postId' => $post->id,
        ]);
    }

    public function storeLocation(LocationPostRequest $request): RedirectResponse
    {
        $post = $this->postController->storeLocation($request);

        return to_route('posts.show', [
            'postId' => $post->id,
        ]);

    }

    public function dashboard(): Response|ResponseFactory
    {
        $posts = $this->postController->dashboard(Auth::user());

        return Inertia::render('Dashboard', [
            'posts' => Inertia::merge($posts->items),
            'nextCursor' => $posts->nextCursor,
            'previousCursor' => $posts->previousCursor,
        ]);
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
    public function update(string $postId, PostRequest $request): RedirectResponse
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
}
