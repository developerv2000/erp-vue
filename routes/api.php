<?php

use App\Models\Manufacturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/manufacturers', fn(Request $request) => Manufacturer::queryFromRequest($request));
});
