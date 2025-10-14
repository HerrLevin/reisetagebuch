<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Backend\LocationController as BackendLocationController;
use App\Http\Controllers\Controller;
use App\Http\Requests\GeocodeRequest;
use App\Jobs\PrefetchJob;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    private BackendLocationController $locationController;

    public function __construct(BackendLocationController $locationController)
    {
        $this->locationController = $locationController;
    }

    private function canStoreHistory(Request $request): bool
    {
        if ($request->hasCookie('rtb_disallow_history') || $request->hasHeader('X-RTB-DISALLOW-HISTORY')) {
            return false;
        }
        if ($request->hasCookie('rtb_allow_history') || $request->hasHeader('X-RTB-ALLOW-HISTORY')) {
            return true;
        }

        return false;
    }

    public function prefetch(float $latitude, float $longitude, Request $request): void
    {
        $point = Point::makeGeodetic($latitude, $longitude);
        if ($this->canStoreHistory($request)) {
            $this->locationController->createTimestampedUserWaypoint($request->user()->id, $point);
        }
        PrefetchJob::dispatch($point);

        abort('204');
    }

    public function getRecentRequestLocation(float $latitude, float $longitude): JsonResponse
    {
        $point = Point::makeGeodetic($latitude, $longitude);
        $location = $this->locationController->getRecentRequestLocation($point);

        if ($location === null) {
            return response()->json(['error' => 'Location not found'], 404);
        }

        return response()->json($location);
    }

    public function geocode(GeocodeRequest $request): JsonResponse
    {
        $point = null;
        if ($request->latitude && $request->longitude) {
            $point = Point::makeGeodetic($request->latitude, $request->longitude);
            if ($this->canStoreHistory($request)) {
                $this->locationController->createTimestampedUserWaypoint($request->user()->id, $point);
            }
        }

        if ($request->provider === 'airport') {
            return response()->json($this->locationController->geocodeAirport($request->input('query'), $point));
        }

        return $this->geocodeMotis($request->input('query'), $point);
    }

    private function geocodeMotis(string $query, ?Point $point): JsonResponse
    {
        try {
            $locations = $this->locationController->geocode($query, $point);
        } catch (ConnectionException $e) {
            return response()->json(['error' => 'Connection error'], 503);
        }

        return response()->json($locations);
    }
}
