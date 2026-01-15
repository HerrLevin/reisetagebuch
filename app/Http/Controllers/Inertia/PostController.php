<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Requests\FilterPostsRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Inertia\Inertia;
use Inertia\Response;
use Inertia\ResponseFactory;

class PostController extends Controller
{
    public function create(): Response|ResponseFactory
    {
        return Inertia::render('NewPostDialog/CreateLocationPost');
    }

    /**
     * @throws AuthorizationException
     */
    public function show(string $postId): Response|ResponseFactory
    {
        return Inertia::render('SinglePost', ['postId' => $postId]);
    }

    public function createTransport(): Response|ResponseFactory
    {
        return Inertia::render('NewPostDialog/CreateTransportPost');
    }

    public function createText(): Response|ResponseFactory
    {
        return Inertia::render('NewPostDialog/CreateTextPost');
    }

    public function dashboard(): Response|ResponseFactory
    {
        return Inertia::render('Dashboard');
    }

    /**
     * @throws AuthorizationException
     */
    public function edit(string $postId): Response|ResponseFactory
    {
        return Inertia::render('EditPost', [
            'postId' => $postId,
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    public function editTransport(): Response|ResponseFactory
    {
        return inertia('NewPostDialog/ListStopovers');
    }

    /**
     * @throws AuthorizationException
     */
    public function editTimesTransport(string $postId): Response|ResponseFactory
    {
        return inertia('NewPostDialog/EditTransportTimes', [
            'postId' => $postId,
        ]);
    }

    public function filter(FilterPostsRequest $request): Response|ResponseFactory
    {
        return Inertia::render('Posts/Filter', [
            'filters' => [
                'dateFrom' => $request->input('dateFrom'),
                'dateTo' => $request->input('dateTo'),
                'visibility' => $request->input('visibility', []),
                'travelReason' => $request->input('travelReason', []),
                'tags' => $request->input('tags', []),
            ],
        ]);
    }
}
