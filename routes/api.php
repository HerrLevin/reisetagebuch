<?php

use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\LocationController as ApiLocationController;
use App\Http\Controllers\Api\MapController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;

Route::get('profile/{username}/map-data', [UserController::class, 'mapData'])->name('profile.mapdata');

Route::middleware('auth:api')->group(function () {
    Route::get('/timeline', [PostController::class, 'timeline'])
        ->name('posts.timeline');

    Route::get('/geocode', [ApiLocationController::class, 'geocode'])
        ->name('posts.create.geocode');
    Route::get('/new/prefetch/{latitude}/{longitude}', [ApiLocationController::class, 'prefetch'])
        ->name('posts.create.prefetch');
    Route::get('/request-location/{latitude}/{longitude}', [ApiLocationController::class, 'getRecentRequestLocation'])
        ->name('api.request-location.get');
    Route::get('/search-nearby', [ApiLocationController::class, 'search'])
        ->name('api.location.search');

    Route::post('/{post}/like', [LikeController::class, 'store'])
        ->name('posts.like');
    Route::delete('/{post}/like', [LikeController::class, 'destroy'])
        ->name('posts.unlike');

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
});
