<?php

namespace App\Http\Controllers\Api;

use App\Dto\PostPaginationDto;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    private \App\Http\Controllers\Backend\PostController $postController;

    public function __construct(\App\Http\Controllers\Backend\PostController $postController)
    {
        $this->postController = $postController;
    }

    public function timeline(): PostPaginationDto
    {
        return $this->postController->dashboard(Auth::user());
    }
}
