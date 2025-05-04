<?php

use App\Http\Controllers\Backend\LocationController as BLocationController;
use App\Http\Controllers\Inertia\AccountController;
use App\Http\Controllers\Inertia\LocationController;
use App\Http\Controllers\Inertia\PostController;
use App\Http\Controllers\Inertia\UserController;
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
    Route::prefix('account')->group(function () {
        Route::get('/', [AccountController::class, 'edit'])->name('account.edit');
        Route::patch('/', [AccountController::class, 'update'])->name('account.update');
        Route::delete('/', [AccountController::class, 'destroy'])->name('account.destroy');
    });

    Route::prefix('posts')->group(callback: function () {
        Route::get('/create', [PostController::class, 'create'])->name('posts.create.post');
        Route::get('/transport/create', [PostController::class, 'createTransport'])->name('posts.create.transport-post');
        Route::post('/transport/create', [PostController::class, 'storeTransport'])->name('posts.create.transport-post.store');
        Route::post('/create', [PostController::class, 'store'])->name('posts.create.post.store');
        Route::get('/locations', [LocationController::class, 'nearby'])->name('posts.create.start');
        Route::get('/departures', [LocationController::class, 'departures'])->name('posts.create.departures');
        Route::get('/stopovers', [LocationController::class, 'stopovers'])->name('posts.create.stopovers');
        Route::get('/new', [PostController::class, 'dashboard'])->name('posts.create.text');
        Route::get('/{postId}', [PostController::class, 'show'])->name('posts.show');
        Route::delete('/{postId}', [PostController::class, 'destroy'])->name('posts.destroy');
        // this belongs in an api
        Route::get('/new/prefetch/{latitude}/{longitude}', [BLocationController::class, 'nearby'])->name('posts.create.prefetch');
    });


    Route::get('/dashboard', [PostController::class, 'dashboard'])->name('dashboard');

    Route::get('/profile/{username}', [UserController::class, 'show'])->name('profile.show');
});

require __DIR__.'/auth.php';
