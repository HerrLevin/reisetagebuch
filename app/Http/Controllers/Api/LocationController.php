<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Backend\LocationController as BackendLocationController;
use App\Http\Controllers\Controller;
use App\Http\Requests\GeocodeRequest;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    private BackendLocationController $locationController;

    public function __construct(BackendLocationController $locationController)
    {
        $this->locationController = $locationController;
    }

    public function prefetch(float $latitude, float $longitude): void
    {
        $point = Point::makeGeodetic($latitude, $longitude);
        $this->locationController->prefetch($point);

        abort('204');
    }

    public function geocode(GeocodeRequest $request): JsonResponse
    {
        $point = null;
        if ($request->latitude && $request->longitude) {
            $point = Point::makeGeodetic($request->latitude, $request->longitude);
        }

        try {
            $locations = $this->locationController->geocode($request->get('query'), $point);
        } catch (ConnectionException $e) {
            return response()->json(['error' => 'Connection error'], 503);
        }

        return response()->json($locations);
    }
}
