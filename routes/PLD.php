<?php

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
});
