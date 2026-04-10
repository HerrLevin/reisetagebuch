<?php

namespace App\Jobs;

use App\Http\Controllers\Backend\LocationController;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\App;
use Throwable;

class PrefetchJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public Point $point;

    private ?LocationController $locationController;

    public function __construct(Point $point, ?LocationController $locationController = null)
    {
        $this->point = $point;
        $this->locationController = $locationController;
    }

    public int $tries = 2;

    public int $retryAfter = 60 * 2;

    public function handle(): void
    {
        try {
            $attempts = method_exists($this, 'attempts') ? $this->attempts() : null;
        } catch (Throwable) {
            $attempts = null;
        }

        if ($this->locationController === null) {
            $this->locationController = App::make(LocationController::class);
        }
        $radius = config('app.overpass.radius');

        if ($attempts !== null && $attempts > 1) {
            $radius = intdiv($radius, 4);
        }

        $this->locationController->prefetch($this->point, $radius);
    }
}
