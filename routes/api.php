<?php

use App\Http\Controllers\global\MainController;
use App\Models\Manufacturer;
use App\Models\Process;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->name('api.')->group(function () {
    // Global
    Route::controller(MainController::class)->group(function () {
        Route::post('upload-wysiwyg-image/{folder}', 'uploadWysiwygImage')->name('upload-wysiwyg-image');
    });

    // Administration
    Route::prefix('/users')->name('users.')->group(function () {
        Route::get('/', fn(Request $request) => User::queryRecordsFromRequest($request, 'paginate', true))->name('get');
    });

    // MAD
    Route::prefix('/manufacturers')->name('manufacturers.')->group(function () {
        Route::get('/', fn(Request $request) => Manufacturer::queryRecordsFromRequest($request, 'paginate', true))->name('get');
    });

    Route::prefix('/products')->name('products.')->group(function () {
        Route::get('/', fn(Request $request) => Product::queryRecordsFromRequest($request, 'paginate', true))->name('get');
    });

    Route::prefix('/processes')->name('processes.')->group(function () {
        Route::get('/', fn(Request $request) => Process::queryRecordsFromRequest($request, 'paginate', true))->name('get');
    });
});
