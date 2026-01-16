<?php

namespace App\Http\Controllers\Inertia;

use Inertia\Response;
use Inertia\ResponseFactory;

class UserController extends Controller
{
    public function show(string $username): Response|ResponseFactory
    {
        return inertia('Profile/Show', ['username' => $username]);
    }

    public function showMap(string $username): Response|ResponseFactory
    {
        return inertia('Profile/ShowMap', [
            'username' => $username,
        ]);
    }
}
