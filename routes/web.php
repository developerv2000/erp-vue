<?php

use App\Http\Controllers\global\AuthenticationController;
use Illuminate\Support\Facades\Route;

// Authentication
Route::controller(AuthenticationController::class)->group(function () {
    Route::get('login', 'loginShow')->middleware('guest')->name('login.show');
    Route::post('login', 'login')->middleware('guest')->name('login');
    Route::post('logout', 'logout')->middleware('auth')->name('logout');
});

Route::get('/', function () {
    return inertia('Home');
});

