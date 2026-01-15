<?php

use App\Http\Controllers\import\ImportInvoiceController;
use App\Http\Controllers\import\ImportProductController;
use App\Http\Controllers\import\ImportShipmentController;
use App\Support\Generators\CRUDRouteGenerator;
use Illuminate\Support\Facades\Route;

Route::prefix('import')->name('import.')->middleware('auth', 'auth.session')->group(function () {
    // Products
    Route::prefix('/products')->controller(ImportProductController::class)->name('products.')->group(function () {
        CRUDRouteGenerator::defineDefaultRoutesOnly(
            ['index', 'edit', 'update'],
            'id',
            'can:view-import-products',
            'can:edit-import-products'
        );
    });

    // Shipments
    Route::prefix('/shipments')->controller(ImportShipmentController::class)->name('shipments.')->group(function () {
        CRUDRouteGenerator::defineDefaultRoutesOnly(
            ['index', 'create', 'store', 'edit', 'update'],
            'id',
            'can:view-import-shipments',
            'can:edit-import-shipments'
        );
    });

    // Invoices
    Route::prefix('/invoices')->controller(ImportInvoiceController::class)->name('invoices.')->group(function () {
        CRUDRouteGenerator::defineDefaultRoutesOnly(
            ['index', 'create', 'store', 'edit', 'update'],
            'id',
            'can:view-ELD-invoices',
            'can:edit-ELD-invoices'
        );
    });
});
