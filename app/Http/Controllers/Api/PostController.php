<?php

namespace App\Http\Controllers\Api;

use App\Dto\PostPaginationDto;
use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use Illuminate\Http\Request;
use Symfony\Component\Mailer\Transport;

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
}
