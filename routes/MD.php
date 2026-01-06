<?php

use App\Http\Controllers\MD\MDSerializedByManufacturerController;
use App\Support\Generators\CRUDRouteGenerator;
use Illuminate\Support\Facades\Route;

Route::prefix('md')->name('md.')->middleware('auth', 'auth.session')->group(function () {
    Route::prefix('/serialized-by-manufacturer')
        ->controller(MDSerializedByManufacturerController::class)
        ->name('serialized-by-manufacturer.')->group(function () {

            CRUDRouteGenerator::defineDefaultRoutesOnly(
                ['index', 'edit', 'update'],
                'id',
                'can:view-MD-serialized-by-manufacturer',
                'can:edit-MD-serialized-by-manufacturer',
            );
        });
});
