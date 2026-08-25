<?php

namespace App\Jobs;

use App\Http\Controllers\Backend\CalculateTransportStatsController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CalculateCountryStatsForPost implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $postId,
    ) {}

    public function handle(CalculateTransportStatsController $controller): void
    {
        $controller->calculateStatsForPost($this->postId, true);
    }
}
