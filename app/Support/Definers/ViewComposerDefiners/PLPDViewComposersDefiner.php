<?php

namespace App\Support\Definers\ViewComposerDefiners;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Inn;
use App\Models\Manufacturer;
use App\Models\MarketingAuthorizationHolder;
use App\Models\Order;
use App\Models\Process;
use App\Models\ProductForm;
use App\Models\User;
use Illuminate\Support\Facades\View;

class PLPDViewComposersDefiner
{
    public static function defineAll()
    {
        self::defineReadyForOrderProcessesComposers();
        self::defineOrdersComposers();
        self::defineOrderProductsComposers();
    }

    /*
    |--------------------------------------------------------------------------
    | Definers
    |--------------------------------------------------------------------------
    */

    private static function defineReadyForOrderProcessesComposers()
    {
        View::composer('PLPD.ready-for-order-processes.partials.filter', function ($view) {
            $view->with([
                'manufacturers' => Manufacturer::getMinifiedRecordsWithProcessesReadyForOrder(),
                'countriesOrderedByProcessesCount' => Country::orderByProcessesCount()->get(),
                'MAHs' => MarketingAuthorizationHolder::orderByName()->get(),
                'inns' => Inn::orderByName()->get(),
                'productForms' => ProductForm::getMinifiedRecordsWithName(),
                'bdmUsers' => User::getCMDBDMsMinifed(),
                'enTrademarks' => Process::pluckAllEnTrademarks(),
                'ruTrademarks' => Process::pluckAllRuTrademarks(),
            ]);
        });
    }

    private static function defineOrdersComposers()
    {
        View::composer('PLPD.orders.partials.filter', function ($view) {
            $view->with(array_merge(self::getDefaultOrdersShareData(), [
                'bdmUsers' => User::getCMDBDMsMinifed(),
                'statusOptions' => Order::getFilterStatusOptions(),
                'orderNames' => Order::onlyWithName()->orderByName()->pluck('name'),
            ]));
        });

        View::composer('PLPD.orders.partials.create-form', function ($view) {
            $view->with(self::getDefaultOrdersShareData());
        });

        View::composer('PLPD.orders.partials.edit-form', function ($view) {
            $view->with(array_merge(self::getDefaultOrdersShareData(), [
                'currencies' => Currency::orderByName()->get(),
                'defaultSelectedCurrencyID' => Currency::getDefaultIdValueForMADProcesses(),
            ]));
        });
    }

    private static function defineOrderProductsComposers()
    {
        View::composer('PLPD.order-products.partials.filter', function ($view) {
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

    private static function getDefaultOrdersShareData()
    {
        return [
            'manufacturers' => Manufacturer::getMinifiedRecordsWithProcessesReadyForOrder(),
            'countriesOrderedByProcessesCount' => Country::orderByProcessesCount()->get(),
        ];
    }
}
