<?php

use App\Models\Manufacturer;
use Illuminate\Support\Facades\Route;

Route::get('/manufacturers', function (Request $request) {
    $records = Manufacturer::withBasicRelations()
        ->withBasicRelationCounts()
        ->paginate(50);

    Manufacturer::appendBasicAttributes($records);

    return $records;
})->middleware('auth:sanctum');
