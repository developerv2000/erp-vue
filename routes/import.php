<?php

use App\Http\Controllers\import\ImportInvoiceController;
use App\Http\Controllers\import\ImportProductController;
use App\Http\Controllers\import\ImportShipmentController;
use App\Support\Generators\CRUDRouteGenerator;
use Illuminate\Support\Facades\Route;

Route::prefix('import')->name('import.')->middleware('auth', 'auth.session')->group(function () {
    // Products
    Route::prefix('/products')->controller(ImportProductController::class)->name('products.')->group(function () {
        Route::get('/', 'index')->name('index')->middleware('can:view-import-products');
    });

    // Shipments
    Route::prefix('/shipments')->controller(ImportShipmentController::class)->name('shipments.')->group(function () {
        CRUDRouteGenerator::defineDefaultRoutesOnly(
            ['index', 'create', 'store', 'edit', 'update', 'destroy'],
            'id',
            'can:view-import-shipments',
            'can:edit-import-shipments'
        );

        // AJAX requests
        Route::middleware('can:edit-import-shipments')->group(function () {
            Route::get('/ready-without-shipment-from-manufacturer-products/{manufacturer_id}', 'getReadyWithoutShipmentFromManufacturerProducts')
                ->name('get-ready-without-shipment-from-manufacturer-products');

            Route::post('/complete/{record}', 'complete')->name('complete');
            Route::post('/arrive-at-warehouse/{record}', 'arriveAtWarehouse')->name('arrive-at-warehouse');
        });
    });

    // Invoices
    Route::prefix('/invoices')->controller(ImportInvoiceController::class)->name('invoices.')->group(function () {
        CRUDRouteGenerator::defineDefaultRoutesOnly(
            ['index', 'create', 'store', 'edit', 'update', 'destroy'],
            'id',
            'can:view-import-invoices',
            'can:edit-import-invoices'
        );

        // AJAX requests
        Route::middleware('can:edit-import-invoices')->group(function () {
            Route::post('/send-for-payment/{record}', 'sendForPayment')->name('send-for-payment');
        });
    });
});
