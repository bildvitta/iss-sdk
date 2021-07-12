<?php

use BildVitta\Hub\Http\Controllers\Auth\CallbackController;
use BildVitta\Hub\Http\Controllers\Auth\LoginController;
use BildVitta\Hub\Http\Controllers\Auth\LogoutController;
use BildVitta\Hub\Http\Controllers\Auth\RefreshController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->middleware('throttle')->group(function () {
    Route::get('login')->name('auth.login')->uses(LoginController::class);
    Route::get('logout')->name('auth.logout')->uses(LogoutController::class);
    Route::get('callback')->name('auth.callback')->uses(CallbackController::class);
    Route::get('refresh')->name('auth.refresh')->uses(RefreshController::class);
});
