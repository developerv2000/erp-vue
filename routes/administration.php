<?php

use App\Http\Controllers\administration\DepartmentController;
use App\Http\Controllers\administration\PermissionController;
use App\Http\Controllers\administration\RoleController;
use App\Http\Controllers\administration\UserController;
use App\Support\Generators\CRUDRouteGenerator;
use Illuminate\Support\Facades\Route;

Route::prefix('administration')->name('administration.')->middleware('auth', 'auth.session', 'can:administrate')->group(function () {
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');

    // Users
    Route::prefix('users')->controller(UserController::class)->name('users.')->group(function () {
        CRUDRouteGenerator::defineDefaultRoutesOnly(
            ['index', 'create', 'store', 'edit', 'update', 'destroy']
        );

        Route::post('/update-password/{record}', 'updatePassword')->name('update-password');
    });
});
