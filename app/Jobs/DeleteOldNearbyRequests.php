<?php

namespace App\Jobs;

use App\Repositories\LocationRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeleteOldNearbyRequests implements ShouldQueue
{
    use Queueable;

    private LocationRepository $locationRepository;

    public function __construct(?LocationRepository $locationRepository = null)
    {
        $this->locationRepository = $locationRepository ?? app(LocationRepository::class);
    }

    public function handle(): void
    {
        $this->locationRepository->deleteOldNearbyRequests();
    }
}
