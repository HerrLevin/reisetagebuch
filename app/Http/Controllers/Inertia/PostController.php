<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Requests\PostCreateRequest;
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
        return Inertia::render('NewPostDialog/CreatePost');
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

    public function storeTransport(TransportPostCreateRequest $request): RedirectResponse
    {
        $post = $this->postController->storeMotisTransport($request);

        return to_route('posts.show', [
            'postId' => $post->id,
        ]);
    }

    public function store(PostCreateRequest $request): RedirectResponse
    {
        $post = $this->postController->store($request);

        return to_route('posts.show', [
            'postId' => $post->id,
        ]);

    }

    public function dashboard(): Response|ResponseFactory
    {
        $posts = $this->postController->dashboard(Auth::user());

        return Inertia::render('Dashboard', [
            'posts' => $posts,
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
