<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use Inertia\Response;
use Inertia\ResponseFactory;

class LocationController extends Controller
{
    public function index(): Response|ResponseFactory
    {
        return inertia('LocationHistory/Index');
    }

    public function nearby(): Response|ResponseFactory
    {
        return inertia('NewPostDialog/ListLocations');
    }

    public function departures(): Response|ResponseFactory
    {
        return inertia('NewPostDialog/ListDepartures');
    }

    public function stopovers(): Response|ResponseFactory
    {
        return inertia('NewPostDialog/ListStopovers');
    }
}
