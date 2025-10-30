<?php

namespace App\Jobs;

use App\Http\Controllers\Traewelling\CrossPostController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class TraewellingCrossCheckInJob implements ShouldQueue
{
    use Queueable;

    private string $postId;

    private CrossPostController $crossPostController;

    public function __construct(string $postId, ?CrossPostController $crossPostController = null)
    {
        $this->postId = $postId;
        $this->crossPostController = $crossPostController ?? app(CrossPostController::class);
    }

    public function handle(): void
    {
        $this->crossPostController->crossCheckIn($this->postId);
    }
}
