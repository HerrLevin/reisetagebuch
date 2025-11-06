<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Backend\MapController as BackendMapController;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class MapController extends Controller
{
    private BackendMapController $locationController;

    public function __construct(BackendMapController $locationController)
    {
        $this->locationController = $locationController;
    }

    public function getLineStringBetween(string $from, string $to): JsonResponse
    {
        $linestring = $this->locationController->fromTo($from, $to);

        if (! $linestring) {
            return response()->json(['error' => 'Invalid stops provided'], 400);
        }

        return response()->json($linestring);
    }

    public function getStopsBetween(string $from, string $to): JsonResponse
    {
        $stops = $this->locationController->stopPointGeometryFromTo($from, $to);

        return response()->json($stops);
    }
}
