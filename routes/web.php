<?php

use App\Http\Controllers\global\AuthenticationController;
use App\Http\Controllers\global\MainController;
use App\Http\Controllers\global\NotificationController;
use App\Http\Controllers\global\ProfileController;
use App\Http\Controllers\global\SettingController;
use Illuminate\Support\Facades\Route;

// Authentication
Route::controller(AuthenticationController::class)->group(function () {
    Route::get('login', 'loginShow')->middleware('guest')->name('login.show');
    Route::post('login', 'login')->middleware('guest')->name('login');
    Route::post('logout', 'logout')->middleware('auth')->name('logout');
});

Route::middleware('auth', 'auth.session')->group(function () {
    Route::controller(MainController::class)->group(function () {
        Route::get('/', 'redirectToHomePage')->name('home');
    });

    Route::controller(SettingController::class)->prefix('/settings')->name('settings.')->group(function () {
        Route::patch('locale', 'updateLocale')->name('update-locale');
        Route::patch('preferred-theme', 'toggleTheme')->name('toggle-theme');
        Route::patch('collapsed-leftbar', 'toggleLeftbar')->name('toggle-leftbar'); // ajax request
        Route::patch('table-columns/{key}', 'updateTableColumns')->name('update-table-columns');
    });

    Route::prefix('notifications')->controller(NotificationController::class)->name('notifications.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::patch('/mark-as-read', 'markAsRead')->name('mark-as-read');
        Route::delete('/destroy', 'destroy')->name('destroy');
    });

    Route::controller(ProfileController::class)->name('profile.')->group(function () {
        Route::get('profile', 'edit')->name('edit');
        Route::patch('profile', 'update')->name('update');
        Route::patch('password', 'updatePassword')->name('update-password');
    });
});
