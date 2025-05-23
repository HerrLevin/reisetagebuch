<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Requests\LocationPostRequest;
use App\Http\Requests\PostRequest;
use App\Http\Requests\TransportPostCreateRequest;
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
}
