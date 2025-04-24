<?php

use App\Http\Controllers\Backend\LocationController as BLocationController;
use App\Http\Controllers\Inertia\LocationController;
use App\Http\Controllers\Inertia\PostController;
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

Route::middleware('auth')->group(callback: function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create.post');
    Route::post('/posts/create', [PostController::class, 'store'])->name('posts.create.post.store');
    Route::get('/dashboard', [PostController::class, 'dashboard'])->name('dashboard');
    Route::get('/posts/locations', [LocationController::class, 'nearby'])->name('posts.create.start');
    Route::get('/posts/departures', [LocationController::class, 'nearby'])->name('posts.create.departures');
    Route::get('/posts/new', [PostController::class, 'dashboard'])->name('posts.create.text');

    // this belongs in an api
    Route::get('/posts/new/prefetch/{latitude}/{longitude}', [BLocationController::class, 'nearby'])->name('posts.create.prefetch');

    // click dummy
    Route::get('/posts/route', function () {
        return Inertia::render('NewPostDialog/ListLocations');
    })->name('posts.create.route');
});

require __DIR__.'/auth.php';
