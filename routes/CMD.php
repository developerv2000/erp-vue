<?php

use App\Http\Controllers\CMD\CMDInvoiceController;
use App\Http\Controllers\CMD\CMDOrderController;
use App\Http\Controllers\CMD\CMDOrderProductController;
use App\Support\Generators\CRUDRouteGenerator;
use Illuminate\Support\Facades\Route;

Route::prefix('cmd')->name('cmd.')->middleware('auth', 'auth.session')->group(function () {
    // Orders
    Route::prefix('/orders')->controller(CMDOrderController::class)->name('orders.')->group(function () {
        CRUDRouteGenerator::defineDefaultRoutesOnly(
            ['index', 'edit', 'update'],
            'id',
            'can:view-CMD-orders',
            'can:edit-CMD-orders'
        );

        // AJAX requests
        Route::middleware('can:edit-CMD-orders')->group(function () {
            Route::post('/sent-to-confirmation/{record}', 'sentToConfirmation')->name('sent-to-confirmation');
            Route::post('/sent-to-manufacturer/{record}', 'sentToManufacturer')->name('sent-to-manufacturer');
            Route::post('/start-production/{record}', 'startProduction')->name('start-production');
        });
    });

    // Order products
    Route::prefix('/orders/products')->controller(CMDOrderProductController::class)->name('order-products.')->group(function () {
        CRUDRouteGenerator::defineDefaultRoutesOnly(
            ['index', 'edit', 'update'],
            'id',
            'can:view-CMD-order-products',
            'can:edit-CMD-order-products'
        );

        // AJAX requests
        Route::middleware('can:edit-CMD-order-products')->group(function () {
            Route::post('/end-production/{record}', 'endProduction')->name('end-production');
            Route::post('/set-as-ready-for-shipment-from-manufacturer/{record}', 'setAsReadyForShipmentFromManufacturer')
                ->name('set-as-ready-for-shipment-from-manufacturer');
        });
    });

    // Invoices
    Route::prefix('/invoices')->controller(CMDInvoiceController::class)->name('invoices.')->group(function () {
        CRUDRouteGenerator::defineDefaultRoutesOnly(
            ['index', 'create', 'store', 'edit', 'update', 'destroy'],
            'id',
            'can:view-CMD-invoices',
            'can:edit-CMD-invoices'
        );

        // AJAX requests
        Route::middleware('can:edit-CMD-invoices')->group(function () {
            Route::post('/send-for-payment/{record}', 'sendForPayment')->name('send-for-payment');
        });
    });
});
