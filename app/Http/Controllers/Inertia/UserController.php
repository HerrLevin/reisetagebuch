<?php

namespace App\Http\Controllers\Inertia;

class UserController extends Controller
{
    public function show(string $userId) {
        return inertia('Profile/Show', [
            'userId' => $userId,
        ]);
    }
}
