<?php

namespace App\Support\Definers\ViewComposerDefiners;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Manufacturer;
use App\Models\MarketingAuthorizationHolder;
use App\Models\Order;
use App\Models\Process;
use App\Models\User;
use Illuminate\Support\Facades\View;

class CMDViewComposersDefiner
{
    public static function defineAll()
    {
        self::defineOrdersComposers();
        self::defineOrderProductsComposers();
    }

    /*
    |--------------------------------------------------------------------------
    | Definers
    |--------------------------------------------------------------------------
    */

    private static function defineOrdersComposers()
    {
        View::composer('CMD.orders.partials.filter', function ($view) {
            $view->with([
                'manufacturers' => Manufacturer::getMinifiedRecordsWithProcessesReadyForOrder(),
                'countriesOrderedByProcessesCount' => Country::orderByProcessesCount()->get(),
                'bdmUsers' => User::getCMDBDMsMinifed(),
                'statusOptions' => Order::getFilterStatusOptions(),
                'orderNames' => Order::onlyWithName()->orderByName()->pluck('name'),
            ]);
        });

        View::composer('CMD.orders.partials.edit-form', function ($view) {
            $view->with([
                'currencies' => Currency::orderByName()->get(),
                'defaultSelectedCurrencyID' => Currency::getDefaultIdValueForMADProcesses(),
            ]);
        });
    }

    private static function defineOrderProductsComposers()
    {
        View::composer('CMD.order-products.partials.filter', function ($view) {
            $view->with([
                'manufacturers' => Manufacturer::getMinifiedRecordsWithProcessesReadyForOrder(),
                'countriesOrderedByProcessesCount' => Country::orderByProcessesCount()->get(),
                'bdmUsers' => User::getCMDBDMsMinifed(),
                'statusOptions' => Order::getFilterStatusOptions(),
                'MAHs' => MarketingAuthorizationHolder::orderByName()->get(),
                'enTrademarks' => Process::pluckAllEnTrademarks(),
                'ruTrademarks' => Process::pluckAllRuTrademarks(),
                'orderNames' => Order::onlyWithName()->orderByName()->pluck('name'),
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Default shared datas
    |--------------------------------------------------------------------------
    */
}
