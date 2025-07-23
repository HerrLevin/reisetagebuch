<?php

namespace App\Jobs;

use App\Http\Controllers\Backend\LocationController;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PrefetchLocationJob implements ShouldQueue
{
    use Queueable;

    private LocationController $locationController;

    private Point $point;

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
