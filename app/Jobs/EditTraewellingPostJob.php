<?php

namespace App\Jobs;

use App\Http\Controllers\Traewelling\CrossPostController;
use App\Http\Resources\PostTypes\TransportPost;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EditTraewellingPostJob implements ShouldQueue
{
    use Queueable;

    private string $postId;

    private CrossPostController $crossPostController;

    public function __construct(TransportPost $transportPost, ?CrossPostController $crossPostController = null)
    {
        $this->postId = (string) $transportPost->id;
        $this->crossPostController = $crossPostController ?? app(CrossPostController::class);
    }

    public function handle(): void
    {
        $this->crossPostController->updatePost($this->postId);
    }
}
