<?php

use App\Http\Controllers\Inertia\AccountController;
use App\Http\Controllers\Inertia\InviteController;
use App\Http\Controllers\Inertia\LocationController;
use App\Http\Controllers\Inertia\NotificationController;
use App\Http\Controllers\Inertia\PostController;
use App\Http\Controllers\Inertia\TripController;
use App\Http\Controllers\Inertia\UserController;
use App\Http\Controllers\TraewellingOAuthController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'reisetagebuchVersion' => config('app.version'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('auth')->group(callback: function () {
    Route::prefix('account')->group(function () {
        Route::get('/', [AccountController::class, 'edit'])->name('account.edit');
    });

    Route::prefix('location-history')->group(function () {
        Route::get('/', [LocationController::class, 'index'])->name('location-history.index');
    });

    Route::prefix('posts')->group(callback: function () {
        Route::get('/filter', [PostController::class, 'filter'])->name('posts.filter');
        Route::get('/create', [PostController::class, 'create'])->name('posts.create.post');
        Route::get('/new', [PostController::class, 'createText'])->name('posts.create.text');
        Route::get('/{postId}/edit', [PostController::class, 'edit'])->name('posts.edit');

        Route::prefix('/transport')->group(callback: function () {
            Route::get('/departures', [LocationController::class, 'departures'])->name('posts.create.departures');
            Route::get('/stopovers', [LocationController::class, 'stopovers'])->name('posts.create.stopovers');
            Route::get('/create', [PostController::class, 'createTransport'])->name('posts.create.transport-post');
            Route::get('/exit/edit', [PostController::class, 'editTransport'])->name('posts.edit.transport-post');
            Route::get('/{postId}/times/edit', [PostController::class, 'editTimesTransport'])->name('posts.edit.transport-times');
        });
        Route::prefix('/location')->group(callback: function () {
            Route::get('/', [LocationController::class, 'nearby'])->name('posts.create.start');
        });
    });

    Route::get('/trips/create', [TripController::class, 'create'])->name('trips.create');

    Route::get('/home', [PostController::class, 'dashboard'])->name('dashboard');

    Route::get('invites', [InviteController::class, 'index'])->name('invites.index');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications');

    Route::prefix('socialite')->group(callback: function () {
        Route::get('/traewelling/connect', [TraewellingOAuthController::class, 'redirectToProvider'])->name('traewelling.connect');
        Route::get('/traewelling/callback', [TraewellingOAuthController::class, 'handleProviderCallback'])->name('traewelling.callback');
    });
});

// Public routes
Route::get('posts/{postId}', [PostController::class, 'show'])->name('posts.show');
Route::get('profile/{username}', [UserController::class, 'show'])->name('profile.show');
Route::get('profile/{username}/map', [UserController::class, 'showMap'])->name('profile.map');

Route::middleware('cache.headers:public;max_age=2628000;etag')->get('/files/{path}', function ($path) {
    $disk = Storage::disk('public');
    if ($disk->exists($path)) {
        return $disk->response($path);
    }

    abort(404);
})->where('path', '.*')->name('files.show');

require __DIR__.'/auth.php';
