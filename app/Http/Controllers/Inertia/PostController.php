<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Requests\PostCreateRequest;
use App\Http\Requests\TransportPostCreateRequest;
use App\Http\Resources\PostResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Inertia\ResponseFactory;

class PostController
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

    public function createTransport(): Response|ResponseFactory
    {
        return Inertia::render('NewPostDialog/CreateTransportPost');
    }

    public function storeTransport(TransportPostCreateRequest $request): RedirectResponse
    {
        $this->postController->storeMotisTransport($request);

        return to_route('dashboard');
    }

    public function store(PostCreateRequest $request): RedirectResponse
    {
        $this->postController->store($request);

        return to_route('dashboard');
    }

    public function dashboard(): Response|ResponseFactory
    {
        $posts = $this->postController->dashboard(Auth::user());

        return Inertia::render('Dashboard', [
            'posts' => PostResource::collection($posts),
        ]);
    }
}
