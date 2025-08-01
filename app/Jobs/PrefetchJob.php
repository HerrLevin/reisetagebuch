<?php

namespace App\Jobs;

use App\Http\Controllers\Backend\LocationController;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PrefetchJob implements ShouldQueue
{
    use Queueable;

    public Point $point;

    private LocationController $locationController;

    public function __construct(Point $point, ?LocationController $locationController = null)
    {
        $this->point = $point;
        $this->locationController = $locationController ?? app(LocationController::class);
    }

    public function handle(): void
    {
        $this->locationController->prefetch($this->point);
    }
}
