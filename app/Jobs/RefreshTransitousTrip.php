<?php

namespace App\Jobs;

use App\Http\Controllers\Backend\LocationController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RefreshTransitousTrip implements ShouldQueue
{
    use Queueable;

    private string $tripId;

    public function __construct(string $tripId)
    {
        $this->tripId = $tripId;
    }

    public function handle(): void
    {
        $locationController = app(LocationController::class);
        $locationController->updateStopoverTimes($this->tripId);
    }
}
