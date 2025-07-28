<?php

use App\Http\Controllers\global\AttachmentController;
use App\Http\Controllers\global\AuthenticationController;
use App\Http\Controllers\global\CommentController;
use App\Http\Controllers\global\MainController;
use App\Http\Controllers\global\NotificationController;
use App\Http\Controllers\global\ProfileController;
use App\Http\Controllers\global\SettingController;
use App\Support\Generators\CRUDRouteGenerator;
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
        Route::patch('update/{key}/{value}', 'updateByKey')->name('update-by-key'); // ajax request
        Route::patch('table-columns/{key}', 'updateTableColumns')->name('update-table-columns');
    });

    Route::prefix('notifications')->controller(NotificationController::class)->name('notifications.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::patch('/mark-as-read', 'markAsRead')->name('mark-as-read');
        Route::delete('/destroy', 'destroy')->name('destroy');
    });

    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::post('/update-personal-data', 'updatePersonalData')->name('update-personal-data');
        Route::post('/update-password', 'updatePassword')->name('update-password');
    });

    Route::prefix('comments')->controller(CommentController::class)->name('comments.')->group(function () {
        Route::get('/view/{commentable_type}/{commentable_id}', 'index')->name('index');

        CRUDRouteGenerator::defineDefaultRoutesOnly(['edit', 'store', 'update', 'destroy'], 'id', null, 'can:edit-comments');
    });

    Route::prefix('attachments')->controller(AttachmentController::class)->name('attachments.')->group(function () {
        Route::get('/view-model-attachments/{attachable_type}/{attachable_id}', 'viewModelAttachments')->name('view-model-attachments');
        Route::get('/show/{record}', 'show')->name('show');
        Route::delete('/destroy', 'destroy')->name('destroy');
    });

    Route::prefix('misc-models')->controller(MiscModelController::class)->name('misc-models.')->group(function () {
        Route::get('/department/{department}/models', 'departmentModels')->name('department-models');
        Route::get('/model/{model}', 'index')->name('index');
        Route::get('/model/{model}/create', 'create')->name('create');
        Route::get('/model/{model}/edit/{id}', 'edit')->name('edit');

        Route::post('/model/{model}/store', 'store')->name('store');
        Route::patch('/model/{model}/update/{id}', 'update')->name('update');
        Route::delete('/model/{model}/destroy', 'destroy')->name('destroy');
    });
});

require __DIR__.'/MGMT.php';
require __DIR__.'/MAD.php';
