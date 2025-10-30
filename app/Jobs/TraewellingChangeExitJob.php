<?php

namespace App\Jobs;

use App\Http\Controllers\Traewelling\CrossPostController;
use App\Http\Resources\PostTypes\TransportPost;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TraewellingChangeExitJob implements ShouldQueue
{
    use Queueable;

    private string $postId;

    private CrossPostController $crossPostController;

    public function __construct(TransportPost $post, ?CrossPostController $crossPostController = null)
    {
        $this->postId = $post->id;
        $this->crossPostController = $crossPostController ?? app(CrossPostController::class);
    }

    public function handle(): void
    {
        $this->crossPostController->changeExit($this->postId);
    }
}
