<?php

use App\Http\Controllers\MAD\MADManufacturerController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/manufacturers', [MADManufacturerController::class, 'get']);
});
