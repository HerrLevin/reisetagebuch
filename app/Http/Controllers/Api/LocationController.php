<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Backend\LocationController as BackendLocationController;
use App\Http\Controllers\Controller;
use Clickbar\Magellan\Data\Geometries\Point;
use function App\Http\Controllers\Inertia\inertia;

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
}
