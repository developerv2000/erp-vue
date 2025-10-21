<?php

use App\Http\Controllers\global\AttachmentController;
use App\Http\Controllers\global\AuthenticationController;
use App\Http\Controllers\global\CommentController;
use App\Http\Controllers\global\ExcelStorageController;
use App\Http\Controllers\global\MainController;
use App\Http\Controllers\global\ProfileController;
use App\Http\Controllers\global\SettingController;
use App\Support\Generators\CRUDRouteGenerator;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::controller(AuthenticationController::class)->group(function () {
    Route::get('login', 'loginShow')->middleware('guest')->name('login.show');
    Route::post('login', 'login')->middleware('guest')->name('login');
    Route::post('logout', 'logout')->middleware('auth')->name('logout');
});

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

    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::post('/update-personal-data', 'updatePersonalData')->name('update-personal-data');
        Route::post('/update-password', 'updatePassword')->name('update-password');
    });

    Route::prefix('comments')->controller(CommentController::class)->name('comments.')->group(function () {
        Route::get('/view-model-comments/{commentable_type}/{commentable_id}', 'viewModelComments')->name('view-model-comments');

        CRUDRouteGenerator::defineDefaultRoutesOnly(
            ['store', 'update', 'destroy'],
            'id',
            null,
            'can:edit-comments'
        );
    });

    Route::prefix('attachments')->controller(AttachmentController::class)->name('attachments.')->group(function () {
        Route::get('/view-model-attachments/{attachable_type}/{attachable_id}', 'viewModelAttachments')->name('view-model-attachments');
        Route::get('/show/{record}', 'show')->name('show');
        Route::post('/destroy', 'destroy')->name('destroy');
    });

    Route::prefix('excel-storage')
        ->controller(ExcelStorageController::class)
        ->middleware('can:export-records-as-excel')
        ->name('excel-storage.')
        ->group(function () {
            // Generate and store an export file for a given model
            Route::post('/{model}/generate', 'generate')->name('generate');

            // Download a previously generated export file
            Route::post('/{model}/download/{filename}', 'download')->name('download');
        });
});

require __DIR__ . '/MAD.php';
