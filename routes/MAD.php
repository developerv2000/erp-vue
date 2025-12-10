<?php

use App\Http\Controllers\MAD\MADManufacturerController;
use App\Http\Controllers\MAD\MADProcessController;
use App\Http\Controllers\MAD\MADProcessStatusHistoryController;
use App\Http\Controllers\MAD\MADProductController;
use App\Http\Controllers\MAD\MADProductSelectionController;
use App\Support\Generators\CRUDRouteGenerator;
use Illuminate\Support\Facades\Route;

Route::prefix('mad')->name('mad.')->middleware('auth', 'auth.session')->group(function () {
    // VP - Product selection
    Route::prefix('product-selection')
        ->controller(MADProductSelectionController::class)
        ->middleware('can:export-records-as-excel')
        ->name('product-selection.')
        ->group(function () {
            // Generate and store an export file for a given model
            Route::post('/{model}/generate', 'generate')->name('generate');

            // Download a previously generated export file
            Route::post('/{model}/download/{filename}', 'download')->name('download');
        });

    // EPP
    Route::prefix('/manufacturers')->controller(MADManufacturerController::class)->name('manufacturers.')->group(function () {
        CRUDRouteGenerator::defineDefaultRoutesOnly(
            ['index', 'create', 'store', 'edit', 'update', 'destroy', 'trash', 'restore'],
            'id',
            'can:view-MAD-EPP',
            'can:edit-MAD-EPP'
        );
    });

    // IVP
    Route::prefix('/products')->controller(MADProductController::class)->name('products.')->group(function () {
        CRUDRouteGenerator::defineDefaultRoutesOnly(
            ['index', 'create', 'store', 'edit', 'update', 'destroy', 'trash', 'restore'],
            'id',
            'can:view-MAD-IVP',
            'can:edit-MAD-IVP'
        );

        Route::post('/get-similar-records', 'getSimilarRecordsForRequest')->name('get-similar-records');  // AJAX request on products.create for uniqness
        Route::post('/get-matched-atx', 'getMatchedATXForRequest')->name('get-matched-atx');  // AJAX request on products.create
    });

    // VPS
    Route::prefix('/processes')->controller(MADProcessController::class)->name('processes.')->group(function () {
        CRUDRouteGenerator::defineDefaultRoutesOnly(
            ['index', 'create', 'store', 'edit', 'update', 'destroy', 'trash', 'restore'],
            'id',
            'can:view-MAD-VPS',
            'can:edit-MAD-VPS'
        );

        // Duplication
        Route::get('/duplicate/{record}', 'duplicate')->name('duplicate')->middleware('can:edit-MAD-VPS');

        // Ajax requests on checkbox toggles
        Route::post('/update-contracted-in-asp-value', 'updateContractedInAspValue')
            ->name('update-contracted-in-asp-value')
            ->middleware('can:control-MAD-ASP-processes');

        Route::post('/update-registered-in-asp-value', 'updateRegisteredInAspValue')
            ->name('update-registered-in-asp-value')
            ->middleware('can:control-MAD-ASP-processes');

        Route::post('/update-ready-for-order-value', 'updateReadyForOrderValue')
            ->name('update-ready-for-order-value')
            ->middleware('can:mark-MAD-VPS-as-ready-for-order');

        // Status history
        Route::prefix('/status-history')
            ->controller(MADProcessStatusHistoryController::class)
            ->name('status-history.')
            ->middleware('can:edit-MAD-VPS-status-history')
            ->group(function () {
                Route::get('/process/{process}', 'index')->name('index');

                CRUDRouteGenerator::defineDefaultRoutesOnly([
                    'edit',
                    'update',
                    'destroy',
                ]);
            });
    });
});
