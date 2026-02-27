<?php

use App\Http\Controllers\TraewellingOAuthController;
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

// OAuth callback routes (these need server-side handling)
Route::middleware('auth')->prefix('socialite')->group(function () {
    Route::get('/traewelling/connect', [TraewellingOAuthController::class, 'redirectToProvider'])->name('traewelling.connect');
    Route::get('/traewelling/callback', [TraewellingOAuthController::class, 'handleProviderCallback'])->name('traewelling.callback');
});

// SPA catch-all — must be last
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
