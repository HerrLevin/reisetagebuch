<?php

use App\Http\Controllers\ActivityPub\ActorController;
use App\Http\Controllers\ActivityPub\InboxController;
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

// ActivityPub discovery endpoints
Route::get('/.well-known/webfinger', [WellKnownController::class, 'webfinger']);
Route::get('/.well-known/nodeinfo', [WellKnownController::class, 'nodeInfoLinks']);
Route::get('/nodeinfo/2.0', [NodeInfoController::class, 'show']);

// ActivityPub actor & content endpoints
Route::get('/ap/users/{username}', [ActorController::class, 'show']);
Route::get('/ap/users/{username}/outbox', [ActorController::class, 'outbox']);
Route::get('/ap/users/{username}/followers', [ActorController::class, 'followers']);
Route::post('/ap/users/{username}/inbox', [InboxController::class, 'handle'])
    ->middleware('activitypub.verify');
Route::get('/ap/posts/{postId}', [ActorController::class, 'note']);

// SPA catch-all — must be last
// Do not catch requests starting with "telescope" (Laravel Telescope routes)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!telescope).*$');
