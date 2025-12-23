<?php

use App\Http\Controllers\PLD\PLDHelperController;
use App\Http\Controllers\PLD\PLDInvoiceController;
use App\Http\Controllers\PLD\PLDOrderController;
use App\Http\Controllers\PLD\PLDOrderProductController;
use App\Http\Controllers\PLD\PLDReadyForOrderProcessController;
use App\Support\Generators\CRUDRouteGenerator;
use Illuminate\Support\Facades\Route;

Route::prefix('pld')->name('pld.')->middleware('auth', 'auth.session')->group(function () {
    //  Ready for order processes
    Route::prefix('/ready-for-order-processes')
        ->controller(PLDReadyForOrderProcessController::class)
        ->name('ready-for-order-processes.')
        ->group(function () {
            Route::get('/', 'index')->name('index')->middleware('can:view-PLD-ready-for-order-processes');
        });

    // Orders
    Route::prefix('/orders')->controller(PLDOrderController::class)->name('orders.')->group(function () {
        CRUDRouteGenerator::defineDefaultRoutesOnly(
            ['index', 'create', 'store', 'edit', 'update', 'destroy'],
            'id',
            'can:view-PLD-orders',
            'can:edit-PLD-orders'
        );
    });

    // Order products
    Route::prefix('/orders/products')->controller(PLDOrderProductController::class)->name('order-products.')->group(function () {
        CRUDRouteGenerator::defineDefaultRoutesOnly(
            ['index', 'edit', 'update', 'destroy'],
            'id',
            'can:view-PLD-order-products',
            'can:edit-PLD-order-products'
        );
    });

    // Invoices
    Route::get('/invoices', [PLDInvoiceController::class, 'index'])->name('invoices.index')->middleware('can:view-PLPD-invoices');

    // Helpers
    Route::prefix('/orders/products')->controller(PLDHelperController::class)->group(function () {
        // AJAX request on orders.create
        Route::get('/ready-for-order-processes-of-manufacturer', 'getReadyForOrderProcessesOfManufacturer')
            ->middleware('can:edit-PLD-orders')
            ->name('get-ready-for-order-processes-of-manufacturer');

        // AJAX request on orders.create
        Route::get('/process-with-it-similar-records-for-order', 'getProcessWithItSimilarRecordsForOrder')
            ->middleware('can:edit-PLD-orders')
            ->name('get-process-with-it-similar-records-for-order');
    });
});
