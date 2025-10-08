<?php

use App\Http\Controllers\MAD\MADManufacturerController;
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
    });
});
