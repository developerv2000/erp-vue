<?php

use App\Http\Controllers\global\CommentController;
use App\Http\Controllers\global\MainController;
use App\Http\Controllers\global\NotificationController;
use App\Http\Controllers\global\ProfileController;
use App\Http\Controllers\global\SettingController;
use App\Support\Generators\CRUDRouteGenerator;
use Illuminate\Support\Facades\Route;

// Authentication routes
require __DIR__ . '/auth.php';

// Global routes
Route::middleware('auth', 'auth.session')->group(function () {
    Route::controller(MainController::class)->group(function () {
        Route::get('/', 'redirectToHomePage')->name('home');
    });

    Route::controller(SettingController::class)->prefix('/settings')->name('settings.')->group(function () {
        Route::post('by-key/{key}/{value}', 'updateByKey')->name('update-by-key'); // AJAX request

        Route::post('table-headers/{key}', 'updateTableHeaders')->name('table-headers.update'); // AJAX request
        Route::post('table-headers/{key}/reset', 'resetTableHeaders')->name('table-headers.reset'); // AJAX request
    });

    Route::prefix('notifications')->controller(NotificationController::class)->name('notifications.')->group(function () {
        Route::get('/', 'index')->name('index');

        Route::post('/mark-as-read', 'markAsRead')->name('mark-as-read'); // AJAX request
        Route::post('/destroy', 'destroy')->name('destroy'); // AJAX request
    });

    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::post('/update-personal-data', 'updatePersonalData')->name('update-personal-data');
        Route::post('/update-password', 'updatePassword')->name('update-password');
    });

    Route::prefix('comments')->controller(CommentController::class)->name('comments.')->group(function () {
        Route::get('/view-model-comments/{commentable_type}/{commentable_id}', 'viewModelComments')->name('view-model-comments');

        Route::post('/store', 'store')->name('store');

        CRUDRouteGenerator::defineDefaultRoutesOnly(
            ['update', 'destroy'],
            'id',
            null,
            'can:edit-comments'
        );
    });
});

require __DIR__ . '/storage.php';
require __DIR__ . '/administration.php';
require __DIR__ . '/MAD.php';
require __DIR__ . '/PLD.php';
require __DIR__ . '/CMD.php';
require __DIR__ . '/PRD.php';
require __DIR__ . '/DD.php';
require __DIR__ . '/MD.php';
