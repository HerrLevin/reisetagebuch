<?php

namespace App\Jobs;

use App\Dto\MotisApi\TripDto;
use App\Http\Controllers\Backend\RerouteStopsController;
use App\Models\TransportTripStop;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RerouteStops implements ShouldQueue
{
    use Queueable;

    private RerouteStopsController $rerouteStopsController;
    private TripDto $tripDto;
    /** @var TransportTripStop[] $stops */
    private array $stops;

    public function __construct(TripDto $tripDto, array $stops, ?RerouteStopsController $rerouteStopsController = null)
    {
        $this->rerouteStopsController = $rerouteStopsController ?? app(RerouteStopsController::class);
        $this->tripDto = $tripDto;
        $this->stops = $stops;
    }

    public function handle(): void
    {
        $this->rerouteStopsController->rerouteStops($this->tripDto, $this->stops);
    }
}
