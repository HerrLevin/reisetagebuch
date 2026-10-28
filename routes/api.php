<?php

use App\Enums\Scopes\ReadScope;
use App\Enums\Scopes\WriteScope;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\ActivityPubFederationController;
use App\Http\Controllers\Api\ActivityPubPostInteractionController;
use App\Http\Controllers\Api\AppConfigurationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CypressController;
use App\Http\Controllers\Api\FollowController;
use App\Http\Controllers\Api\ImprintController;
use App\Http\Controllers\Api\InviteController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\LocationController as ApiLocationController;
use App\Http\Controllers\Api\MapController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\PrivacyPolicyController;
use App\Http\Controllers\Api\TraewellingOAuthController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserSettingsController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::prefix('auth')->group(function () {
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::get('/invite/{code}', [AuthController::class, 'validateInvite']);
});

Route::get('profile/{username}', [UserController::class, 'show'])->middleware(ReadScope::Accounts->middleware());

Route::prefix('statistics')->middleware('auth:api')->group(function () {
    Route::get('countries', [CountryController::class, 'index'])->middleware(ReadScope::Statistics->middleware())->name('statistics.countries');
});

Route::prefix('users')->group(function () {
    Route::get('search', [UserController::class, 'search'])->middleware(ReadScope::Search->middleware())->name('users.search');

    Route::prefix('{userId}')->group(function () {
        Route::get('map-data', [UserController::class, 'mapData'])->middleware(ReadScope::Statuses->middleware())->name('profile.mapdata');
        Route::get('posts', [PostController::class, 'postsForUser'])->middleware(ReadScope::Statuses->middleware())->name('profile.posts');
        Route::prefix('followers')->group(function () {
            Route::get('/', [FollowController::class, 'getFollowers'])->middleware(ReadScope::Follows->middleware())->name('profile.followers');
            Route::post('/{targetId}', [FollowController::class, 'createFollow'])->middleware(WriteScope::Follows->middleware())->name('profile.follow');
            Route::delete('/{targetId}', [FollowController::class, 'deleteFollow'])->middleware(WriteScope::Follows->middleware())->name('profile.unfollow');
        });
        Route::prefix('followings')->group(function () {
            Route::get('/', [FollowController::class, 'getFollowings'])->middleware(ReadScope::Follows->middleware())->name('profile.followings');
            Route::delete('/{targetId}', [FollowController::class, 'deleteFollow'])->middleware(WriteScope::Follows->middleware())->name('profile.remove-follower');
        });
        Route::prefix('follow-requests')->group(function () {
            Route::get('/', [FollowController::class, 'getFollowRequests'])->middleware(ReadScope::Follows->middleware())->name('profile.followrequests');
            Route::post('/{targetId}', [FollowController::class, 'createFollowRequest'])->middleware(WriteScope::Follows->middleware())->name('profile.followrequest');
            Route::put('/{targetId}', [FollowController::class, 'approveFollowRequest'])->middleware(WriteScope::Follows->middleware())->name('profile.followrequest.update');
            Route::delete('/{targetId}', [FollowController::class, 'deleteFollowRequest'])->middleware(WriteScope::Follows->middleware())->name('profile.unfollowrequest');
        });
        Route::prefix('activitypub')->group(function () {
            Route::get('followers', [ActivityPubFederationController::class, 'followersForUser'])->middleware(ReadScope::Follows->middleware())->name('profile.activitypub.followers');
            Route::get('following', [ActivityPubFederationController::class, 'followingForUser'])->middleware(ReadScope::Follows->middleware())->name('profile.activitypub.following');
            Route::prefix('follow-requests')->group(function () {
                Route::put('/{requestId}', [ActivityPubFederationController::class, 'approveFollowRequest'])->middleware(WriteScope::Follows->middleware())->name('profile.activitypub.followrequest.approve');
                Route::delete('/{requestId}', [ActivityPubFederationController::class, 'rejectFollowRequest'])->middleware(WriteScope::Follows->middleware())->name('profile.activitypub.followrequest.reject');
            });
        });
    });
});

Route::prefix('posts/{post}')->group(function () {
    Route::get('/', [PostController::class, 'show'])
        ->middleware(ReadScope::Statuses->middleware())->name('api.posts.show');
    Route::get('/likes', [LikeController::class, 'index'])
        ->middleware(ReadScope::Favourites->middleware())->name('posts.likes');
});

Route::get('/posts/{postId}/transport/stopovers', [PostController::class, 'getStopoversForTransportPost'])
    ->middleware(ReadScope::Statuses->middleware())->name('posts.transport.stopovers.list');

Route::prefix('map')->group(function () {
    Route::get('/linestring', [MapController::class, 'getLineStringBetween'])
        ->name('posts.get.linestring');
    Route::get('/stopovers', [MapController::class, 'getStopsBetween'])
        ->name('posts.get.stopovers');
});

Route::prefix('app')->group(function () {
    Route::get('configuration', [AppConfigurationController::class, 'index'])
        ->name('app.configuration');

    Route::get('imprint', [ImprintController::class, 'show'])
        ->name('imprint.show');

    Route::get('privacy-policy', [PrivacyPolicyController::class, 'show'])
        ->name('privacy-policy.show');
    Route::get('privacy-policy/upcoming', [PrivacyPolicyController::class, 'upcoming'])
        ->name('privacy-policy.upcoming');
});

Route::middleware('auth:api')->group(function () {
    // Auth routes (authenticated) - always accessible, even without accepting the current privacy policy
    Route::prefix('auth')->group(function () {
        Route::get('/user', [AuthController::class, 'user'])->middleware(ReadScope::Accounts->middleware())->name('auth.user');
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::put('/password', [AuthController::class, 'updatePassword'])->middleware(WriteScope::Security->middleware());
        Route::post('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail']);
        Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail']);
    });

    Route::post('app/privacy-policy/{privacyPolicy}/accept', [PrivacyPolicyController::class, 'accept'])
        ->name('privacy-policy.accept');

    // Account viewing/deletion - always accessible, even without accepting the current privacy policy
    Route::prefix('account')->group(function () {
        Route::get('/', [AccountController::class, 'show'])->middleware(ReadScope::Accounts->middleware())->name('account.show');
        Route::delete('/', [AccountController::class, 'destroy'])->middleware(WriteScope::Security->middleware())->name('account.destroy');
    });

    // Everything below requires the currently effective privacy policy to be accepted
    Route::middleware('privacy-policy')->group(function () {
        Route::prefix('socialite')->group(function () {
            Route::get('/traewelling/connect', [TraewellingOAuthController::class, 'redirectToProvider'])->middleware(WriteScope::Security->middleware())->name('traewelling.connect');
            Route::get('/traewelling/callback', [TraewellingOAuthController::class, 'handleProviderCallback'])->middleware(WriteScope::Security->middleware())->name('traewelling.callback');
        });

        Route::get('/timeline', [PostController::class, 'timeline'])
            ->middleware(ReadScope::Statuses->middleware())->name('posts.timeline');

        Route::get('/timeline/global', [PostController::class, 'globalTimeline'])
            ->middleware(ReadScope::Statuses->middleware())->name('posts.timeline.global');

        Route::post('/trips', [TripController::class, 'store'])
            ->middleware(WriteScope::Statuses->middleware())->name('trips.store');

        Route::get('/geocode', [ApiLocationController::class, 'geocode'])
            ->middleware(ReadScope::Search->middleware())->name('posts.create.geocode');
        Route::get('/location/prefetch', [ApiLocationController::class, 'prefetch'])
            ->middleware(WriteScope::Statuses->middleware())->name('posts.create.prefetch');
        Route::get('/location/request-location', [ApiLocationController::class, 'getRecentRequestLocation'])
            ->middleware(ReadScope::LocationHistory->middleware())->name('api.request-location.get');

        Route::prefix('posts')->group(function () {
            Route::get('/', [PostController::class, 'index'])->middleware(ReadScope::Statuses->middleware())->name('api.posts.filter');
            Route::post('/text', [PostController::class, 'storeText'])->middleware(WriteScope::Statuses->middleware())->name('posts.create.text-post.store');
            Route::post('/location', [PostController::class, 'storeLocation'])->middleware(WriteScope::Statuses->middleware())->name('posts.create.post.store');
            Route::post('/mass-edit', [PostController::class, 'massEdit'])->middleware(WriteScope::Statuses->middleware())->name('api.posts.mass-edit');

            Route::post('/transport', [PostController::class, 'storeTransport'])->middleware(WriteScope::Statuses->middleware())->name('posts.create.transport-post.store');
            Route::get('/transport/active', [PostController::class, 'getActiveTransportPost'])->middleware(ReadScope::Statuses->middleware())->name('posts.transport.active');

            Route::prefix('{postId}')->group(function () {
                Route::post('/likes', [LikeController::class, 'store'])
                    ->middleware(WriteScope::Favourites->middleware())->name('posts.like');
                Route::delete('/likes', [LikeController::class, 'destroy'])
                    ->middleware(WriteScope::Favourites->middleware())->name('posts.unlike');

                Route::patch('/', [PostController::class, 'update'])->middleware(WriteScope::Statuses->middleware())->name('posts.update');
                Route::delete('/', [PostController::class, 'destroy'])
                    ->middleware(WriteScope::Statuses->middleware())->name('api.posts.destroy');

                Route::prefix('transport')->group(function () {
                    Route::put('/exit', [PostController::class, 'updateTransportPostExit'])->middleware(WriteScope::Statuses->middleware())->name('posts.update.transport-post');
                    Route::post('/track', [PostController::class, 'uploadTransportTrack'])->middleware(WriteScope::Statuses->middleware())->name('posts.upload.transport-track');
                    Route::delete('/track', [PostController::class, 'deleteTransportTrack'])->middleware(WriteScope::Statuses->middleware())->name('posts.delete.transport-track');

                    Route::prefix('stopovers')->group(function () {
                        Route::prefix('{stopId}')->group(function () {
                            Route::post('/arrival', [PostController::class, 'logStopoverArrival'])->middleware(WriteScope::Statuses->middleware())->name('posts.transport.stopovers.arrival');
                            Route::post('/departure', [PostController::class, 'logStopoverDeparture'])->middleware(WriteScope::Statuses->middleware())->name('posts.transport.stopovers.departure');
                            Route::delete('/arrival', [PostController::class, 'clearStopoverArrival'])->middleware(WriteScope::Statuses->middleware())->name('posts.transport.stopovers.arrival.clear');
                            Route::delete('/departure', [PostController::class, 'clearStopoverDeparture'])->middleware(WriteScope::Statuses->middleware())->name('posts.transport.stopovers.departure.clear');
                        });
                    });
                });

                Route::prefix('traewelling')->group(function () {
                    Route::post('/retry', [PostController::class, 'retryTraewellingCrosspost'])->middleware(WriteScope::Statuses->middleware())->name('posts.traewelling.retry');
                });
            });
        });

        Route::prefix('notifications')->group(function () {
            Route::get('/list', [NotificationController::class, 'index'])
                ->middleware(ReadScope::Notifications->middleware())->name('notifications.index');
            Route::get('/unread-count', [NotificationController::class, 'unreadCount'])
                ->middleware(ReadScope::Notifications->middleware())->name('notifications.unread-count');
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])
                ->middleware(WriteScope::Notifications->middleware())->name('notifications.read');
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])
                ->middleware(WriteScope::Notifications->middleware())->name('notifications.read-all');
        });

        Route::prefix('locations')->group(function () {
            Route::get('/nearby', [ApiLocationController::class, 'search'])
                ->middleware(ReadScope::Search->middleware())->name('api.location.search');
            Route::get('/history', [ApiLocationController::class, 'index'])
                ->middleware(ReadScope::LocationHistory->middleware())->name('api.location.history');
            Route::post('/history', [ApiLocationController::class, 'createHistoryLocation'])
                ->middleware(WriteScope::LocationHistory->middleware())->name('api.location.history.create');
            Route::get('/departures', [ApiLocationController::class, 'departures'])
                ->middleware(ReadScope::Search->middleware())->name('api.location.departures');
            Route::get('/stopovers', [ApiLocationController::class, 'stopovers'])
                ->middleware(ReadScope::Search->middleware())->name('api.location.stopovers');
        });

        Route::prefix('invites')->group(function () {
            Route::get('/', [InviteController::class, 'index'])->middleware(ReadScope::Invites->middleware())->name('api.invites.index');
            Route::post('/', [InviteController::class, 'store'])->middleware(WriteScope::Invites->middleware())->name('api.invites.store');
            Route::delete('/{inviteCode}', [InviteController::class, 'destroy'])->middleware(WriteScope::Invites->middleware())->name('api.invites.destroy');
        });

        Route::prefix('activitypub')->group(function () {
            Route::get('/resolve', [ActivityPubFederationController::class, 'resolve'])->middleware(ReadScope::Search->middleware())->name('ap.federation.resolve');
            Route::get('/following', [ActivityPubFederationController::class, 'following'])->middleware(ReadScope::Follows->middleware())->name('ap.federation.following');
            Route::post('/follow', [ActivityPubFederationController::class, 'follow'])->middleware(WriteScope::Follows->middleware())->name('ap.federation.follow');
            Route::delete('/follow', [ActivityPubFederationController::class, 'unfollow'])->middleware(WriteScope::Follows->middleware())->name('ap.federation.unfollow');

            Route::prefix('posts/{postId}')->group(function () {
                Route::post('/likes', [ActivityPubPostInteractionController::class, 'like'])->middleware(WriteScope::Favourites->middleware())->name('ap.posts.like');
                Route::delete('/likes', [ActivityPubPostInteractionController::class, 'unlike'])->middleware(WriteScope::Favourites->middleware())->name('ap.posts.unlike');
            });
        });

        Route::prefix('account')->group(function () {
            Route::patch('/settings', [UserSettingsController::class, 'update'])->middleware(WriteScope::Accounts->middleware())->name('account.settings.update');
            Route::patch('/profile', [UserController::class, 'update'])->middleware(WriteScope::Accounts->middleware())->name('profile.update');
            Route::post('/profile/avatar', [UserController::class, 'updateAvatar'])->middleware(WriteScope::Accounts->middleware())->name('profile.update.avatar'); // needs to be post b/c of file upload
            Route::delete('/profile/avatar', [UserController::class, 'deleteAvatar'])->middleware(WriteScope::Accounts->middleware())->name('profile.delete.avatar');
            Route::post('/profile/header', [UserController::class, 'updateHeader'])->middleware(WriteScope::Accounts->middleware())->name('profile.update.header'); // needs to be post b/c of file upload
            Route::delete('/profile/header', [UserController::class, 'deleteHeader'])->middleware(WriteScope::Accounts->middleware())->name('profile.delete.header');
            Route::patch('/', [AccountController::class, 'update'])->middleware(WriteScope::Security->middleware())->name('account.update');

            Route::prefix('socialite')->group(callback: function () {
                Route::delete('/traewelling', [AccountController::class, 'disconnectTraewelling'])->middleware(WriteScope::Security->middleware())->name('traewelling.disconnect');
            });
        });
    });
});

if (config('app.testing.cypress')) {
    Route::prefix('cypress')->group(function () {
        Route::post('/seed', [CypressController::class, 'seed']);
        Route::post('/reset', [CypressController::class, 'reset']);
        Route::post('/register', [CypressController::class, 'register']);
    });
}
