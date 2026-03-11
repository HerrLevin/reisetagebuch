<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AppConfigurationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\InviteController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\LocationController as ApiLocationController;
use App\Http\Controllers\Api\MapController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\TraewellingOAuthController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserSettingsController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::get('/invite/{code}', [AuthController::class, 'validateInvite']);
});

Route::get('profile/{username}', [UserController::class, 'show']);

Route::prefix('users')->group(function () {
    Route::prefix('{userId}')->group(function () {
        Route::get('map-data', [UserController::class, 'mapData'])->name('profile.mapdata');
        Route::get('posts', [PostController::class, 'postsForUser'])->name('profile.posts');
        Route::prefix('followers')->group(function () {
            Route::get('/', [FollowController::class, 'getFollowers'])->name('profile.followers');
            Route::post('/{targetId}', [FollowController::class, 'createFollow'])->name('profile.follow');
            Route::delete('/{targetId}', [FollowController::class, 'deleteFollow'])->name('profile.unfollow');
        });
        Route::prefix('followings')->group(function () {
            Route::get('/', [FollowController::class, 'getFollowings'])->name('profile.followings');
            Route::delete('/{targetId}', [FollowController::class, 'deleteFollow'])->name('profile.remove-follower');
        });
    });
});

Route::prefix('posts/{post}')->group(function () {
    Route::get('/', [PostController::class, 'show'])
        ->name('api.posts.show');
    Route::get('/likes', [LikeController::class, 'index'])
        ->name('posts.likes');
});

Route::get('app/configuration', [AppConfigurationController::class, 'index'])
    ->name('app.configuration');

Route::middleware('auth:api')->group(function () {
    Route::prefix('socialite')->group(function () {
        Route::get('/traewelling/connect', [TraewellingOAuthController::class, 'redirectToProvider'])->name('traewelling.connect');
        Route::get('/traewelling/callback', [TraewellingOAuthController::class, 'handleProviderCallback'])->name('traewelling.callback');
    });

    // Auth routes (authenticated)
    Route::prefix('auth')->group(function () {
        Route::get('/user', [AuthController::class, 'user'])->name('auth.user');
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::put('/password', [AuthController::class, 'updatePassword']);
        Route::post('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail']);
        Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail']);
    });

    Route::get('/timeline', [PostController::class, 'timeline'])
        ->name('posts.timeline');

    Route::get('/timeline/global', [PostController::class, 'globalTimeline'])
        ->name('posts.timeline.global');

    Route::post('/trips', [TripController::class, 'store'])
        ->name('trips.store');

    Route::get('/geocode', [ApiLocationController::class, 'geocode'])
        ->name('posts.create.geocode');
    Route::get('/location/prefetch', [ApiLocationController::class, 'prefetch'])
        ->name('posts.create.prefetch');
    Route::get('/location/request-location', [ApiLocationController::class, 'getRecentRequestLocation'])
        ->name('api.request-location.get');

    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index'])->name('api.posts.filter');
        Route::post('/text', [PostController::class, 'storeText'])->name('posts.create.text-post.store');
        Route::post('/location', [PostController::class, 'storeLocation'])->name('posts.create.post.store');
        Route::post('/mass-edit', [PostController::class, 'massEdit'])->name('api.posts.mass-edit');

        Route::prefix('{postId}')->group(function () {
            Route::post('/likes', [LikeController::class, 'store'])
                ->name('posts.like');
            Route::delete('/likes', [LikeController::class, 'destroy'])
                ->name('posts.unlike');

            Route::patch('/', [PostController::class, 'update'])->name('posts.update');
            Route::delete('/', [PostController::class, 'destroy'])
                ->name('api.posts.destroy');

            Route::prefix('transport')->group(function () {
                Route::put('/exit', [PostController::class, 'updateTransportPostExit'])->name('posts.update.transport-post');
                Route::put('/times', [PostController::class, 'updateTimesTransport'])->name('posts.update.transport-times');
            });
        });

        Route::post('/transport', [PostController::class, 'storeTransport'])->name('posts.create.transport-post.store');
    });

    Route::prefix('map')->group(function () {
        Route::get('/linestring', [MapController::class, 'getLineStringBetween'])
            ->name('posts.get.linestring');
        Route::get('/stopovers', [MapController::class, 'getStopsBetween'])
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

    Route::prefix('account')->group(function () {
        Route::get('/', [AccountController::class, 'show'])->name('account.show');
        Route::patch('/settings', [UserSettingsController::class, 'update'])->name('account.settings.update');
        Route::patch('/profile', [UserController::class, 'update'])->name('profile.update');
        Route::post('/profile/avatar', [UserController::class, 'updateAvatar'])->name('profile.update.avatar'); // needs to be post b/c of file upload
        Route::delete('/profile/avatar', [UserController::class, 'deleteAvatar'])->name('profile.delete.avatar');
        Route::post('/profile/header', [UserController::class, 'updateHeader'])->name('profile.update.header'); // needs to be post b/c of file upload
        Route::delete('/profile/header', [UserController::class, 'deleteHeader'])->name('profile.delete.header');
        Route::patch('/', [AccountController::class, 'update'])->name('account.update');
        Route::delete('/', [AccountController::class, 'destroy'])->name('account.destroy');

        Route::prefix('socialite')->group(callback: function () {
            Route::delete('/traewelling', [AccountController::class, 'disconnectTraewelling'])->name('traewelling.disconnect');
        });
    });
});
