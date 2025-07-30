<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Backend\LocationController as BackendLocationController;
use App\Http\Controllers\Controller;
use App\Http\Requests\GeocodeRequest;
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

    public function prefetch(float $latitude, float $longitude, Request $request): void
    {
        $point = Point::makeGeodetic($latitude, $longitude);
        $this->locationController->createTimestampedUserWaypoint($request->user()->id, $point);
        $this->locationController->prefetch($point);

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
            $this->locationController->createTimestampedUserWaypoint($request->user()->id, $point);
        }

        try {
            $locations = $this->locationController->geocode($request->get('query'), $point);
        } catch (ConnectionException $e) {
            return response()->json(['error' => 'Connection error'], 503);
        }

        return response()->json($locations);
    }
}
