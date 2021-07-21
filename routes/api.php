<?php

use BildVitta\Hub\Http\Controllers\Auth\CallbackController;
use BildVitta\Hub\Http\Controllers\Auth\LoginController;
use BildVitta\Hub\Http\Controllers\Auth\LogoutController;
use BildVitta\Hub\Http\Controllers\Auth\RefreshController;
use BildVitta\Hub\Http\Controllers\Users\MeController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/auth')->middleware(['throttle'])->group(function () {
    Route::get('login')->name('auth.login')->uses(LoginController::class);
    Route::get('callback')->name('auth.callback')->uses(CallbackController::class);

    Route::middleware('hub.auth')->group(function () {
        Route::get('logout')->name('auth.logout')->uses(LogoutController::class);
        Route::get('refresh')->name('auth.refresh')->uses(RefreshController::class);
    });
});

Route::prefix('api/users/')->middleware('hub.auth')->group(function () {
    Route::get('me')->name('users.me')->uses(MeController::class);
});
