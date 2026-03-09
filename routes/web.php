<?php

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

// SPA catch-all — must be last
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
