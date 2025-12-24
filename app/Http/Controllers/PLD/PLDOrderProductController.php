<?php

namespace App\Http\Controllers\PLD;

use App\Http\Controllers\Controller;
use App\Http\Requests\PLD\PLDOrderProductStoreRequest;
use App\Http\Requests\PLD\PLDOrderProductUpdateRequest;
use App\Models\Country;
use App\Models\Manufacturer;
use App\Models\MarketingAuthorizationHolder;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Process;
use App\Models\SerializationType;
use App\Models\User;
use App\Support\Traits\Controller\DestroysModelRecords;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PLDOrderProductController extends Controller
{
    use DestroysModelRecords;

    // Required for DestroysModelRecords trait
    public static $model = OrderProduct::class;

    public function index(Request $request)
    {
        $getAllTableHeaders = fn() => $request->user()->collectTranslatedTableHeadersByKey(User::PLD_ORDER_PRODUCTS_HEADERS_KEY);
        $getVisibleHeaders = fn() => User::filterOnlyVisibleTableHeaders($getAllTableHeaders());

        return Inertia::render('departments/PLD/pages/order-products/Index', [
            // Lazy loads. Refetched only on headers update and locale change
            'allTableHeaders' => $getAllTableHeaders,
            'tableVisibleHeaders' => $getVisibleHeaders,

            // Lazy loads. Never refetched again
            'filterDependencies' => fn() => $this->getfilterDependencies(),
        ]);
    }

    public function edit(OrderProduct $record)
    {
        $record->appendBasicAttributes();
        $record->append('title'); // Used on generating breadcrumbs

        return Inertia::render('departments/PLD/pages/order-products/Edit', [
            // Refetched after record update
            'record' => $record,

            // Lazy loads. Never refetched again
            'serializationTypes' => SerializationType::defaultOrdered()->get(),
        ]);
    }

    /**
     * AJAX request
     */
    public function update(PLDOrderProductUpdateRequest $request, OrderProduct $record)
    {
        $record->updateByPLDFromRequest($request);

        return response()->json([
            'success' => true,
        ]);
    }

    private function getfilterDependencies(): array
    {
        return [
            'manufacturers' => Manufacturer::getMinifiedRecordsWithProcessesReadyForOrder(),
            'countriesOrderedByProcessesCount' => Country::orderByProcessesCount()->get(),
            'bdmUsers' => User::getCMDBDMsMinifed(),
            'statusOptions' => Order::getFilterStatusOptions(),
            'MAHs' => MarketingAuthorizationHolder::orderByName()->get(),
            'enTrademarks' => Process::pluckAllEnTrademarks(),
            'ruTrademarks' => Process::pluckAllRuTrademarks(),
            'orderNames' => Order::onlyWithName()->orderByName()->pluck('name'),
        ];
    }
}
