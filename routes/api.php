<?php

use App\Http\Controllers\Api\InviteController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\LocationController as ApiLocationController;
use App\Http\Controllers\Api\MapController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('profile')->group(function () {
    Route::prefix('{username}')->group(function () {
        Route::get('map-data', [UserController::class, 'mapData'])->name('profile.mapdata');
        Route::get('posts', [PostController::class, 'postsForUsername'])->name('profile.posts');
        Route::get('', [UserController::class, 'show']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::get('/timeline', [PostController::class, 'timeline'])
        ->name('posts.timeline');

    Route::get('/geocode', [ApiLocationController::class, 'geocode'])
        ->name('posts.create.geocode');
    Route::get('/new/prefetch/{latitude}/{longitude}', [ApiLocationController::class, 'prefetch'])
        ->name('posts.create.prefetch');
    Route::get('/request-location/{latitude}/{longitude}', [ApiLocationController::class, 'getRecentRequestLocation'])
        ->name('api.request-location.get');

    Route::prefix('posts')->group(function () {
        Route::post('/', [PostController::class, 'storeText'])->name('posts.create.text-post.store');

        Route::post('/location', [PostController::class, 'storeLocation'])->name('posts.create.post.store');

        Route::get('/filter', [PostController::class, 'filter'])->name('api.posts.filter');
        Route::post('/mass-edit', [PostController::class, 'massEdit'])->name('api.posts.mass-edit');
        Route::prefix('{post}')->group(function () {
            Route::get('/', [PostController::class, 'show'])
                ->name('api.posts.show');
            Route::patch('/', [PostController::class, 'update'])->name('posts.update');

            Route::post('/like', [LikeController::class, 'store'])
                ->name('posts.like');
            Route::delete('/like', [LikeController::class, 'destroy'])
                ->name('posts.unlike');
            Route::delete('/', [PostController::class, 'destroy'])
                ->name('api.posts.destroy');
        });

        Route::prefix('transport')->group(function () {
            Route::post('/', [PostController::class, 'storeTransport'])->name('posts.create.transport-post.store');
            Route::put('/{postId}', [PostController::class, 'updateTransport'])->name('posts.update.transport-post');
            Route::put('/{postId}/times', [PostController::class, 'updateTimesTransport'])->name('posts.update.transport-times');
        });
    });

    Route::prefix('map')->group(function () {
        Route::get('/linestring/{from}/{to}', [MapController::class, 'getLineStringBetween'])
            ->name('posts.get.linestring');
        Route::get('/stopovers/{from}/{to}', [MapController::class, 'getStopsBetween'])
            ->name('posts.get.stopovers');
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/list', [NotificationController::class, 'index'])
            ->name('notifications.index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])
            ->name('notifications.unread-count');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])
            ->name('notifications.read-all');
    });

    Route::prefix('locations')->group(function () {
        Route::get('/nearby', [ApiLocationController::class, 'search'])
            ->name('api.location.search');
        Route::get('/history', [ApiLocationController::class, 'index'])
            ->name('api.location.history');
        Route::get('/departures', [ApiLocationController::class, 'departures'])
            ->name('api.location.departures');
        Route::get('/stopovers', [ApiLocationController::class, 'stopovers'])
            ->name('api.location.stopovers');
    });

    Route::prefix('invites')->group(function () {
        Route::get('/', [InviteController::class, 'index'])->name('api.invites.index');
        Route::post('/', [InviteController::class, 'store'])->name('api.invites.store');
        Route::delete('/{inviteCode}', [InviteController::class, 'destroy'])->name('api.invites.destroy');
    });
});
