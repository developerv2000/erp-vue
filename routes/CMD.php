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
    });

    // Order products
    Route::prefix('/orders/products')->controller(CMDOrderProductController::class)->name('order-products.')->group(function () {
        CRUDRouteGenerator::defineDefaultRoutesOnly(
            ['index', 'edit', 'update'],
            'id',
            'can:view-CMD-order-products',
            'can:edit-CMD-order-products'
        );
    });

    // Invoices
    Route::prefix('/invoices')->controller(CMDInvoiceController::class)->name('invoices.')->group(function () {
        CRUDRouteGenerator::defineDefaultRoutesOnly(
            ['index', 'create', 'store', 'edit', 'update', 'destroy'],
            'id',
            'can:view-CMD-invoices',
            'can:edit-CMD-invoices'
        );
    });
});
