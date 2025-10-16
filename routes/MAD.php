<?php

use App\Http\Controllers\MAD\MADManufacturerController;
use App\Http\Controllers\MAD\MADProcessController;
use App\Http\Controllers\MAD\MADProcessStatusHistoryController;
use App\Http\Controllers\MAD\MADProductController;
use App\Support\Generators\CRUDRouteGenerator;
use Illuminate\Support\Facades\Route;

Route::middleware('auth', 'auth.session')->prefix('mad')->name('mad.')->group(function () {
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

        // Status history
        Route::prefix('/{process}/status-history')
            ->controller(MADProcessStatusHistoryController::class)
            ->name('status-history.')
            ->middleware('can:edit-MAD-VPS-status-history')
            ->group(function () {
                CRUDRouteGenerator::defineDefaultRoutesOnly([
                    'index',
                    'edit',
                    'update',
                    'destroy'
                ]);
            });
    });
});
