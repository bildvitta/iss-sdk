<?php

use BildVitta\Hub\Http\Controllers\Auth\CallbackController;
use BildVitta\Hub\Http\Controllers\Auth\LoginController;
use BildVitta\Hub\Http\Controllers\Auth\LogoutController;
use BildVitta\Hub\Http\Controllers\Auth\RefreshController;
use BildVitta\Hub\Http\Controllers\Users\ClearCacheController;
use BildVitta\Hub\Http\Controllers\Users\MeController;
use BildVitta\Hub\Http\Controllers\Users\MeEditController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/auth')->middleware(['throttle'])->group(function () {
    Route::get('login')->name('hub.auth.login')->uses(LoginController::class);
    Route::get('callback')->name('hub.auth.callback')->uses(CallbackController::class);

    Route::middleware('hub.auth')->group(function () {
        Route::get('logout')->name('hub.auth.logout')->uses(LogoutController::class);
        Route::get('refresh')->name('hub.auth.refresh')->uses(RefreshController::class);
    });
});

Route::prefix('api/users/')->middleware('hub.auth')->group(function () {
    Route::get('me')->name('hub.users.me')->uses(MeController::class);
    Route::get('me/edit')->name('hub.users.edit')->uses(MeEditController::class);
});

// Callback used to clear cached user data
Route::prefix('api/callback')->group(function () {
    Route::post('{user}/clear-cache')->name('hub.user.clear-cache')->uses(ClearCacheController::class);
});
