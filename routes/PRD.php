<?php

use App\Http\Controllers\PRD\PRDInvoiceController;
use App\Http\Controllers\PRD\PRDInvoiceProductionTypeController;
use App\Support\Generators\CRUDRouteGenerator;
use Illuminate\Support\Facades\Route;

Route::prefix('prd')->name('prd.')->middleware('auth', 'auth.session')->group(function () {
    // Invoices
    Route::prefix('/invoices')->name('invoices.')->group(function () {
        // General actions
        Route::controller(PRDInvoiceController::class)
            ->middleware('can:edit-PRD-invoices')
            ->group(function () {
                // AJAX requests
                Route::post('/accept/{record}', 'accept')->name('accept');
                Route::post('/complete-payment/{record}', 'completePayment')->name('complete-payment');
            });

        // Production types
        Route::prefix('/production-types')
            ->name('production-types.')
            ->controller(PRDInvoiceProductionTypeController::class)
            ->group(function () {
                Route::get('/', 'index')->middleware('can:view-PRD-invoices')->name('index');

                CRUDRouteGenerator::defineDefaultRoutesOnly(
                    ['edit', 'update'],
                    'id',
                    'can:view-PRD-invoices',
                    'can:edit-PRD-invoices'
                );
            });
    });
});
