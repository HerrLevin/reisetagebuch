<?php

namespace App\Jobs;

use App\Http\Controllers\Backend\CalculateTransportStatsController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CalculateStatsForTransportPost implements ShouldQueue
{
    use Queueable;

    private string $postId;

    public function __construct(string $postId)
    {
        $this->postId = $postId;
    }

    public function handle(): void
    {
        app(CalculateTransportStatsController::class)->calculateStatsForPost($this->postId);
    }
}
