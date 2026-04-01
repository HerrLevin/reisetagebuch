<?php

use App\Http\Controllers\ActivityPub\MastodonActivityPubController;
use App\Http\Controllers\ActivityPub\NodeInfoController;
use App\Http\Controllers\ActivityPub\WellKnownController;
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

Route::prefix('ap')->name('ap.')->group(function () {
    Route::get('users/{username}', [MastodonActivityPubController::class, 'actor'])->name('actor');
    Route::get('/users/{username}/outbox', [MastodonActivityPubController::class, 'outbox'])->name('outbox');
    Route::post('/users/{username}/inbox', [MastodonActivityPubController::class, 'inbox'])->name('inbox');
    Route::get('/posts/{id}/object', [MastodonActivityPubController::class, 'postObject'])->name('object');
});

// SPA catch-all — must be last
// Do not catch requests starting with "telescope" (Laravel Telescope routes)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!telescope).*$');
