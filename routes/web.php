<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // click dummy

    Route::get('/posts/create', function () {
        return Inertia::render('NewPostDialog/CreatePost');
    })->name('posts.create.post');

    Route::get('/posts/new', function () {
        return Inertia::render('NewPostDialog/ListLocations');
    })->name('posts.create.start');

    Route::get('/posts/route', function () {
        return Inertia::render('NewPostDialog/ListLocations');
    })->name('posts.create.route');
});

require __DIR__.'/auth.php';
