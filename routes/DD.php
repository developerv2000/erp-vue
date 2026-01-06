<?php

use App\Http\Controllers\DD\DDOrderProductController;
use App\Support\Generators\CRUDRouteGenerator;
use Illuminate\Support\Facades\Route;

Route::prefix('dd')->name('dd.')->middleware('auth', 'auth.session')->group(function () {
    Route::prefix('/order-products')
        ->controller(DDOrderProductController::class)
        ->name('order-products.')->group(function () {

            CRUDRouteGenerator::defineDefaultRoutesOnly(
                ['index', 'edit', 'update'],
                'id',
                'can:view-DD-order-products',
                'can:edit-DD-order-products',
            );
        });
});
