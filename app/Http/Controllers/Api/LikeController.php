<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Backend\LikeController as Backend;
use App\Models\Post;
use Illuminate\Http\Request;

class LikeController
{
    private Backend $likeController;

    public function __construct(Backend $likeController)
    {
        $this->likeController = $likeController;
    }

    public function store(Request $request, Post $post)
    {
        $response = $this->likeController->store($request->user(), $post);

        return response()->json([
            'liked' => $response->likedByUser,
            'likes_count' => $response->likeCount,
        ]);
    }

    public function destroy(Request $request, Post $post)
    {
        $response = $this->likeController->destroy($request->user(), $post);

        return response()->json([
            'liked' => $response->likedByUser,
            'likes_count' => $response->likeCount,
        ]);
    }
}
