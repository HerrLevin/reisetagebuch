<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Notifications/Index');
    }
}
