<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Backend\TripController as BackendTripController;
use App\Http\Requests\StoreTripRequest;
use Illuminate\Http\JsonResponse;

class TripController extends Controller
{
    private BackendTripController $tripController;

    public function __construct(BackendTripController $tripController)
    {
        parent::__construct();
        $this->tripController = $tripController;
    }

    public function store(StoreTripRequest $request): JsonResponse
    {
        try {
            $trip = $this->tripController->store($request);
            $origin = $trip->stops->first()->location;

            return response()->json([
                'success' => true,
                'tripId' => $trip->foreign_trip_id,
                'startId' => $origin->id,
                'startTime' => $request->departureTime,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create trip: '.$e->getMessage(),
            ], 422);
        }
    }
}
