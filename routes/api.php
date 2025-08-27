<?php

use App\Models\Manufacturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::prefix('/manufacturers')->name('manufacturers.')->group(function () {
        Route::get('/', fn(Request $request) => Manufacturer::queryFromRequest($request))->name('get');
        Route::post('/store', fn(Request $request) => Manufacturer::storeFromRequest($request))->name('store');
        Route::post('/update/{manufacturer}', fn(Request $request) => Manufacturer::updateFromRequest($request))->name('update');
    });
});
