<?php

namespace App\Http\Controllers\DD;

use App\Http\Controllers\Controller;
use App\Http\Requests\DD\DDOrderProductUpdateRequest;
use App\Models\Country;
use App\Models\Manufacturer;
use App\Models\MarketingAuthorizationHolder;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Process;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DDOrderProductController extends Controller
{
    public function index(Request $request)
    {
        $getAllTableHeaders = fn() => $request->user()->collectTranslatedTableHeadersByKey(User::DD_ORDER_PRODUCTS_HEADERS_KEY);
        $getVisibleHeaders = fn() => User::filterOnlyVisibleTableHeaders($getAllTableHeaders());

        return Inertia::render('departments/DD/pages/order-products/Index', [
            // Lazy loads. Refetched only on headers update and locale change
            'allTableHeaders' => $getAllTableHeaders,
            'tableVisibleHeaders' => $getVisibleHeaders,

            // Lazy loads. Never refetched again
            'filterDependencies' => fn() => $this->getFilterDependencies(),
        ]);
    }

    public function edit($record)
    {
        $record = OrderProduct::withBasicDDRelations()
            ->withBasicDDRelationCounts()
            ->findorfail($record);

        $record->appendBasicDDAttributes();
        $record->append('title'); // Used on generating breadcrumbs

        return Inertia::render('departments/DD/pages/order-products/Edit', [
            // Refetched after record update
            'record' => $record,
        ]);
    }

    /**
     * AJAX request
     */
    public function update(DDOrderProductUpdateRequest $request, OrderProduct $record)
    {
        $record->updateByDDFromRequest($request);

        return response()->json([
            'success' => true,
        ]);
    }

    private function getFilterDependencies(): array
    {
        return [
            'manufacturers' => Manufacturer::getMinifiedRecordsWithProcessesReadyForOrder(),
            'countriesOrderedByProcessesCount' => Country::orderByProcessesCount()->get(),
            'MAHs' => MarketingAuthorizationHolder::orderByName()->get(),
            'enTrademarks' => Process::pluckAllEnTrademarks(),
            'ruTrademarks' => Process::pluckAllRuTrademarks(),
            'orderNames' => Order::onlyWithName()->orderByName()->pluck('name'),
        ];
    }
}
