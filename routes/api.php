<?php

use App\Http\Controllers\global\MainController;
use App\Models\Invoice;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Process;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->name('api.')->group(function () {
    // Global
    Route::prefix('/notifications')->name('notifications.')->group(function () {
        Route::get('/', fn(Request $request) => auth()->user()->queryNotificationsFromRequest($request, 'paginate'))
            ->name('get');

        Route::get('/unread-count', fn() => auth()->user()->unreadNotifications()->count())
            ->name('unread-count');
    });

    Route::controller(MainController::class)->group(function () {
        Route::post('upload-wysiwyg-image/{folder}', 'uploadWysiwygImage')->name('upload-wysiwyg-image');
    });

    // Administration
    Route::prefix('/users')->name('users.')->group(function () {
        Route::get('/', fn(Request $request) => User::queryRecordsFromRequest($request, 'paginate', true))
            ->middleware('can:administrate')
            ->name('get');
    });

    // MAD
    Route::prefix('/manufacturers')->name('manufacturers.')->group(function () {
        Route::get('/', fn(Request $request) => Manufacturer::queryRecordsFromRequest($request, 'paginate', true))
            ->middleware('can:view-MAD-EPP')
            ->name('get');
    });

    Route::prefix('/products')->name('products.')->group(function () {
        Route::get('/', fn(Request $request) => Product::queryRecordsFromRequest($request, 'paginate', true))
            ->middleware('can:view-MAD-IVP')
            ->name('get');
    });

    Route::prefix('/processes')->name('processes.')->group(function () {
        Route::get('/', fn(Request $request) => Process::queryRecordsFromRequest($request, 'paginate', true))
            ->middleware('can:view-MAD-VPS')
            ->name('get');
    });

    // PLD
    Route::prefix('/pld')->name('pld.')->group(function () {
        Route::prefix('/ready-for-order-processes')->name('ready-for-order-processes.')->group(function () {
            Route::get('/', fn(Request $request) => Process::queryReadyForOrderRecordsFromRequest($request, 'paginate', true))
                ->middleware('can:view-PLD-ready-for-order-processes')
                ->name('get');
        });

        Route::prefix('/orders')->name('orders.')->group(function () {
            Route::get('/', fn(Request $request) => Order::queryPLDRecordsFromRequest($request, 'paginate', true))
                ->middleware('can:view-PLD-orders')
                ->name('get');
        });

        Route::prefix('/order-products')->name('order-products.')->group(function () {
            Route::get('/', fn(Request $request) => OrderProduct::queryPLDRecordsFromRequest($request, 'paginate', true))
                ->middleware('can:view-PLD-order-products')
                ->name('get');
        });

        Route::prefix('/invoices')->name('invoices.')->group(function () {
            Route::get('/', fn(Request $request) => Invoice::queryPLDRecordsFromRequest($request, 'paginate', true))
                ->middleware('can:view-PLD-invoices')
                ->name('get');
        });
    });

    // CMD
    Route::prefix('/cmd')->name('cmd.')->group(function () {
        Route::prefix('/orders')->name('orders.')->group(function () {
            Route::get('/', fn(Request $request) => Order::queryCMDRecordsFromRequest($request, 'paginate', true))
                ->middleware('can:view-CMD-orders')
                ->name('get');
        });

        Route::prefix('/order-products')->name('order-products.')->group(function () {
            Route::get('/', fn(Request $request) => OrderProduct::queryCMDRecordsFromRequest($request, 'paginate', true))
                ->middleware('can:view-CMD-order-products')
                ->name('get');
        });

        Route::prefix('/invoices')->name('invoices.')->group(function () {
            Route::get('/', fn(Request $request) => Invoice::queryCMDRecordsFromRequest($request, 'paginate', true))
                ->middleware('can:view-CMD-invoices')
                ->name('get');
        });
    });

    // PRD
    Route::prefix('/prd')->name('prd.')->group(function () {
        Route::prefix('/invoices')->name('invoices.')->group(function () {
            Route::prefix('/production-types')->name('production-types.')->group(function () {
                Route::get('/', fn(Request $request) => Invoice::queryPRDProductionTypeRecordsFromRequest($request, 'paginate', true))
                    ->middleware('can:view-PRD-invoices')
                    ->name('get');
            });

            Route::prefix('/import-types')->name('import-types.')->group(function () {
                Route::get('/', fn(Request $request) => Invoice::queryPRDImportTypeRecordsFromRequest($request, 'paginate', true))
                    ->middleware('can:view-PRD-invoices')
                    ->name('get');
            });
        });
    });

    // DD
    Route::prefix('/dd')->name('dd.')->group(function () {
        Route::prefix('/order-products')->name('order-products.')->group(function () {
            Route::get('/', fn(Request $request) => OrderProduct::queryDDRecordsFromRequest($request, 'paginate', true))
                ->middleware('can:view-DD-order-products')
                ->name('get');
        });
    });

    // MD
    Route::prefix('/md')->name('md.')->group(function () {
        Route::prefix('/serialized-by-manufacturer')->name('serialized-by-manufacturer.')->group(function () {
            Route::get('/', fn(Request $request) => OrderProduct::queryMDRecordsFromRequest($request, 'paginate', true))
                ->middleware('can:view-MD-serialized-by-manufacturer')
                ->name('get');
        });
    });

    // import
    Route::prefix('/import')->name('import.')->group(function () {
        Route::prefix('/products')->name('products.')->group(function () {
            Route::get('/', fn(Request $request) => OrderProduct::queryImportRecordsFromRequest($request, 'paginate', true))
                ->middleware('can:view-import-products')
                ->name('get');
        });

        Route::prefix('/shipments')->name('shipments.')->group(function () {
            Route::get('/', fn(Request $request) => Shipment::queryImportRecordsFromRequest($request, 'paginate', true))
                ->middleware('can:view-import-shipments')
                ->name('get');
        });

        Route::prefix('/invoices')->name('invoices.')->group(function () {
            Route::get('/', fn(Request $request) => Invoice::queryImportRecordsFromRequest($request, 'paginate', true))
                ->middleware('can:view-import-invoices')
                ->name('get');
        });
    });
});
