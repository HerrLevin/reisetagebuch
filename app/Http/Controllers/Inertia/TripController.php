<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class TripController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Trips/Create');
    }
}
