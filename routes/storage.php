<?php

use App\Http\Controllers\global\AttachmentController;
use App\Http\Controllers\global\ExcelStorageController;
use App\Http\Controllers\global\PrivateStorageController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth', 'auth.session')->group(function () {
    // Attachment management routes
    Route::prefix('attachments')->controller(AttachmentController::class)->name('attachments.')->group(function () {
        Route::get('/view-model-attachments/{attachable_type}/{attachable_id}', 'viewModelAttachments')
            ->name('view-model-attachments');

        Route::get('/show/{record}', 'show')->name('show');
        Route::post('/destroy', 'destroy')->name('destroy');
    });

    // Generating and exporting Excel files for various models
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

    // Private storage route for accessing order product files
    Route::get('/order-products/files/{path}', [PrivateStorageController::class, 'getOrderProductFile'])
        ->where('path', '.*')
        ->name('order-products.files');

    // Private storage route for accessing invoice files
    Route::get('/invoices/files/{path}', [PrivateStorageController::class, 'getInvoiceFile'])
        ->where('path', '.*')
        ->name('invoices.files');
});
