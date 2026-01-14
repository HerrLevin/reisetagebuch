<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Backend\TripController as BackendTripController;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTripRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TripController extends Controller
{
    private BackendTripController $tripController;

    public function __construct(BackendTripController $tripController)
    {
        $this->tripController = $tripController;
    }

    public function index()
    {
        return Inertia::render('Trips/Create');
    }

    public function create(): Response
    {
        return Inertia::render('Trips/Create');
    }

    public function store(StoreTripRequest $request): RedirectResponse
    {
        try {
            $trip = $this->tripController->store($request);
            $origin = $trip->stops->first()->location;
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['trip' => 'Could not create trip: '.$e->getMessage()])->withInput();
        }

        return redirect()->route('posts.create.stopovers', ['tripId' => $trip->foreign_trip_id, 'startId' => $origin->id, 'startTime' => $request->departureTime]);
    }
}
