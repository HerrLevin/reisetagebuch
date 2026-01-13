<?php

namespace App\Http\Controllers\Api;

use App\Dto\ErrorDto;
use App\Http\Controllers\Backend\MapController as BackendMapController;
use App\Http\Controllers\Controller;
use Clickbar\Magellan\Data\Geometries\MultiPoint;

class MapController extends Controller
{
    private BackendMapController $locationController;

    public function __construct(BackendMapController $locationController)
    {
        $this->locationController = $locationController;
    }

    public function getLineStringBetween(string $from, string $to)
    {
        $linestring = $this->locationController->fromTo($from, $to);

        if (! $linestring) {
            abort(400, new ErrorDto('Invalid stops provided'));
        }

        return $linestring;
    }

    public function getStopsBetween(string $from, string $to): ?MultiPoint
    {
        return $this->locationController->stopPointGeometryFromTo($from, $to);
    }
}
