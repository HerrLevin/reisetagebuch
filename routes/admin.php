<?php

use App\Http\Controllers\Api\ImprintController;
use App\Http\Controllers\Api\PrivacyPolicyController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix('app')->group(function () {
    Route::post('privacy-policy', [PrivacyPolicyController::class, 'store'])
        ->middleware('admin')
        ->name('privacy-policy.store');

    // Also requires the currently effective privacy policy to be accepted, like the rest of the app.
    Route::middleware('privacy-policy')->group(function () {
        Route::patch('imprint', [ImprintController::class, 'update'])
            ->middleware('admin')
            ->name('imprint.update');
    });
});
