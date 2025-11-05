<?php

use App\Http\Controllers\Api\LocationController as ApiLocationController;
use App\Http\Controllers\Api\MapController;
use App\Http\Controllers\Inertia\AccountController;
use App\Http\Controllers\Inertia\InviteController;
use App\Http\Controllers\Inertia\LocationController;
use App\Http\Controllers\Inertia\PostController;
use App\Http\Controllers\Inertia\TripController;
use App\Http\Controllers\Inertia\UserController;
use App\Http\Controllers\Inertia\UserSettingsController;
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
        Route::patch('/', [AccountController::class, 'update'])->name('account.update');
        Route::delete('/', [AccountController::class, 'destroy'])->name('account.destroy');
    });

    Route::prefix('settings')->group(function () {
        Route::patch('/', [UserSettingsController::class, 'update'])->name('account.settings.update');
    });

    Route::prefix('location-history')->group(function () {
        Route::get('/', [LocationController::class, 'index'])->name('location-history.index');
    });

    Route::prefix('posts')->group(callback: function () {
        Route::get('/filter', [PostController::class, 'filter'])->name('posts.filter');
        Route::get('/create', [PostController::class, 'create'])->name('posts.create.post');
        Route::post('/create', [PostController::class, 'storeText'])->name('posts.create.text-post.store');
        Route::get('/new', [PostController::class, 'createText'])->name('posts.create.text');
        Route::post('/mass-edit', [PostController::class, 'massEdit'])->name('posts.mass-edit');
        Route::delete('/{postId}', [PostController::class, 'destroy'])->name('posts.destroy');
        Route::patch('/{postId}', [PostController::class, 'update'])->name('posts.update');
        Route::get('/{postId}/edit', [PostController::class, 'edit'])->name('posts.edit');

        Route::prefix('/transport')->group(callback: function () {
            Route::get('/departures', [LocationController::class, 'departures'])->name('posts.create.departures');
            Route::get('/stopovers', [LocationController::class, 'stopovers'])->name('posts.create.stopovers');
            Route::get('/geocode', [ApiLocationController::class, 'geocode'])->name('posts.create.geocode');
            Route::get('/create', [PostController::class, 'createTransport'])->name('posts.create.transport-post');
            Route::post('/create', [PostController::class, 'storeTransport'])->name('posts.create.transport-post.store');
            Route::get('/{postId}/edit', [PostController::class, 'editTransport'])->name('posts.edit.transport-post');
            Route::get('/{postId}/times/edit', [PostController::class, 'editTimesTransport'])->name('posts.edit.transport-times');
            Route::put('/{postId}/times', [PostController::class, 'updateTimesTransport'])->name('posts.update.transport-times');
            Route::put('/{postId}', [PostController::class, 'updateTransport'])->name('posts.update.transport-post');
        });
        Route::prefix('/location')->group(callback: function () {
            Route::post('/create', [PostController::class, 'storeLocation'])->name('posts.create.post.store');
            Route::get('/', [LocationController::class, 'nearby'])->name('posts.create.start');
        });
        // this belongs in an api
        Route::get('/new/prefetch/{latitude}/{longitude}', [ApiLocationController::class, 'prefetch'])->name('posts.create.prefetch');
        Route::get('/request-location/{latitude}/{longitude}', [ApiLocationController::class, 'getRecentRequestLocation'])->name('api.request-location.get');
    });

    Route::resource('trips', TripController::class);

    Route::get('/home', [PostController::class, 'dashboard'])->name('dashboard');

    Route::post('profile/{username}', [UserController::class, 'update'])->name('profile.update');

    Route::get('invites', [InviteController::class, 'index'])->name('invites.index');
    Route::post('invites', [InviteController::class, 'store'])->name('invites.store');
    Route::delete('invites/{inviteCode}', [InviteController::class, 'destroy'])->name('invites.destroy');

    Route::prefix('socialite')->group(callback: function () {
        Route::get('/traewelling/connect', [TraewellingOAuthController::class, 'redirectToProvider'])->name('traewelling.connect');
        Route::get('/traewelling/callback', [TraewellingOAuthController::class, 'handleProviderCallback'])->name('traewelling.callback');
        Route::get('/traewelling/disconnect', [AccountController::class, 'disconnectTraewelling'])->name('traewelling.disconnect');
    });
});

// Public routes
Route::get('posts/{postId}', [PostController::class, 'show'])->name('posts.show');
Route::get('profile/{username}', [UserController::class, 'show'])->name('profile.show');
Route::get('profile/{username}/map', [UserController::class, 'showMap'])->name('profile.map');
Route::get('profile/{username}/map-data', [UserController::class, 'mapData'])->name('profile.mapdata');

Route::prefix('map')->group(function () {
    Route::get('/linestring/{from}/{to}', [MapController::class, 'getLineStringBetween'])->name('posts.get.linestring');
});

Route::middleware('cache.headers:public;max_age=2628000;etag')->get('/files/{path}', function ($path) {
    $disk = Storage::disk('public');
    if ($disk->exists($path)) {
        return $disk->response($path);
    }

    abort(404);
})->where('path', '.*')->name('files.show');

require __DIR__.'/auth.php';
