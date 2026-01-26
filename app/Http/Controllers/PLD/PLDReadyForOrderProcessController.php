<?php

namespace App\Http\Controllers\PLD;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Inn;
use App\Models\Manufacturer;
use App\Models\MarketingAuthorizationHolder;
use App\Models\Process;
use App\Models\ProductForm;
use App\Models\User;
use App\Support\Helpers\ControllerHelper;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class PLDReadyForOrderProcessController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('departments/PLD/pages/ready-for-order-processes/Index', [
            // Refetched only on locale change
            'allTableHeaders' => fn() => $this->getAllTableHeadersTranslated(),

            // Lazy loads. Never refetched again
            'filterDependencies' => fn() => $this->getFilterDependencies(),
        ]);
    }

    private function getAllTableHeadersTranslated(): Collection
    {
        $headers = collect([
            ['title' => 'fields.BDM', 'key' => 'manufacturer_bdm', 'width' => 146, 'sortable' => false],
            ['title' => 'dates.Readiness', 'key' => 'readiness_for_order_date', 'width' => 144, 'sortable' => true],
            ['title' => 'Manufacturer', 'key' => 'product_manufacturer_name', 'width' => 140, 'sortable' => false],
            ['title' => 'fields.Country', 'key' => 'country_id', 'width' => 80, 'sortable' => true],
            ['title' => 'fields.MAH', 'key' => 'marketing_authorization_holder_id', 'width' => 102, 'sortable' => true],

            ['title' => 'fields.TM Eng', 'key' => 'trademark_en', 'width' => 110, 'sortable' => true],
            ['title' => 'fields.TM Rus', 'key' => 'trademark_ru', 'width' => 110, 'sortable' => true],
            ['title' => 'fields.Generic', 'key' => 'product_inn_name', 'width' => 180, 'sortable' => true],
            ['title' => 'fields.Form', 'key' => 'product_form_name', 'width' => 120, 'sortable' => true],
            ['title' => 'fields.Dosage', 'key' => 'product_dosage', 'width' => 120, 'sortable' => true],
            ['title' => 'fields.Pack', 'key' => 'product_pack', 'width' => 120, 'sortable' => false],

            ['title' => 'Orders', 'key' => 'order_products_count', 'width' => 120, 'sortable' => false],
            ['title' => 'ID', 'key' => 'id', 'width' => 62, 'sortable' => true],
        ]);

        ControllerHelper::translateTableHeadersTitle($headers);

        return $headers;
    }

    private function getFilterDependencies(): array
    {
        return [
            'manufacturers' => Manufacturer::getMinifiedRecordsWithProcessesReadyForOrder(),
            'countriesOrderedByProcessesCount' => Country::orderByProcessesCount()->get(),
            'MAHs' => MarketingAuthorizationHolder::orderByName()->get(),
            'inns' => Inn::orderByName()->get(),
            'productForms' => ProductForm::getMinifiedRecordsWithName(),
            'bdmUsers' => User::getCMDBDMsMinifed(),
            'enTrademarks' => Process::pluckAllEnTrademarks(),
            'ruTrademarks' => Process::pluckAllRuTrademarks(),
        ];
    }
}
