<?php

use App\Http\Controllers\ActivityPub\MastodonActivityPubController;
use App\Http\Controllers\ActivityPub\NodeInfoController;
use App\Http\Controllers\ActivityPub\WellKnownController;
use App\Http\Middleware\VerifyHttpSignature;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// File serving route
Route::middleware('cache.headers:public;max_age=2628000;etag')->get('/files/{path}', function ($path) {
    $disk = Storage::disk('public');
    if ($disk->exists($path)) {
        return $disk->response($path);
    }

    abort(404);
})->where('path', '.*')->name('files.show');

Route::prefix('.well-known')->name('well-known.')->group(function () {
    Route::get('nodeinfo', [WellKnownController::class, 'nodeInfo']);
    Route::get('webfinger', [WellKnownController::class, 'webfinger']);
});

Route::get('nodeinfo/2.0', [NodeInfoController::class, 'nodeInfo20']);

Route::prefix('ap')
    ->name('ap.')
    // ActivityPub endpoints are meant to be accessed by federated systems and
    // external actors, so they should not require a web CSRF token. Exclude
    // Laravel's VerifyCsrfToken middleware for this route group.
    ->withoutMiddleware([PreventRequestForgery::class])
    ->group(function () {
        Route::get('users/{username}', [MastodonActivityPubController::class, 'actor'])->name('actor');
        Route::get('users/{username}/outbox', [MastodonActivityPubController::class, 'outbox'])->name('outbox');
        Route::get('users/{username}/followers', [MastodonActivityPubController::class, 'followers'])->name('followers');
        Route::get('users/{username}/following', [MastodonActivityPubController::class, 'following'])->name('following');
        Route::post('users/{username}/inbox', [MastodonActivityPubController::class, 'inbox'])
            ->middleware([VerifyHttpSignature::class, 'throttle:60,1'])
            ->name('inbox');
        Route::post('inbox', [MastodonActivityPubController::class, 'sharedInbox'])
            ->middleware([VerifyHttpSignature::class, 'throttle:60,1'])
            ->name('shared-inbox');
        Route::get('posts/{id}', [MastodonActivityPubController::class, 'postObject'])->name('post');
        Route::get('posts/{id}/object', [MastodonActivityPubController::class, 'postObject'])->name('post-object');
        Route::get('activities/{id}', function ($id) {
            return response()->json(['error' => 'Not implemented'], 404);
        })->name('activity');
    });

// SPA catch-all — must be last
// Do not catch requests starting with "telescope" (Laravel Telescope routes)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!telescope).*$');
