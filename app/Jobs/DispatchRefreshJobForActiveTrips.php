<?php

namespace App\Jobs;

use App\Repositories\TransportTripRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class DispatchRefreshJobForActiveTrips implements ShouldQueue
{
    use Queueable;

    private TransportTripRepository $transportTripRepository;

    public function __construct(?TransportTripRepository $transportTripRepository = null)
    {
        $this->transportTripRepository = $transportTripRepository ?? app(TransportTripRepository::class);
    }

    public function handle(): void
    {
        $foreignIds = $this->transportTripRepository->getActiveTrips('transitous');
        Log::debug('Dispatching refresh jobs for active trips', ['count' => count($foreignIds)]);
        foreach ($foreignIds as $foreignTripId) {
            Log::info('Dispatching refresh job for transitous trip '.$foreignTripId);
            RefreshTransitousTrip::dispatch($foreignTripId);
        }
    }
}
