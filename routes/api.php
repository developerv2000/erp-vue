<?php

use App\Http\Controllers\global\MainController;
use App\Models\Manufacturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->name('api.')->group(function () {
    Route::controller(MainController::class)->group(function () {
        Route::post('upload-wysiwyg-image/{folder}', 'uploadWysiwygImage')->name('upload-wysiwyg-image');
    });

    Route::prefix('/manufacturers')->name('manufacturers.')->group(function () {
        Route::get('/', fn(Request $request) => Manufacturer::queryFromRequest($request))->name('get');
    });
});
