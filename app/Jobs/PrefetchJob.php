<?php

namespace App\Jobs;

use App\Http\Controllers\Backend\LocationController;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\App;

class PrefetchJob implements ShouldQueue
{
    use Queueable;

    public Point $point;

    private ?LocationController $locationController;

    public function __construct(Point $point, ?LocationController $locationController = null)
    {
        $this->point = $point;
        $this->locationController = $locationController;
    }

    public function handle(): void
    {
        // we can't inject the controller in the constructor because of serialization issues
        if ($this->locationController === null) {
            $this->locationController = App::make(LocationController::class);
        }
        $radius = config('app.recent_location.radius');
        $this->locationController->prefetch($this->point, $radius);
    }
}
