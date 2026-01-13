<?php

namespace App\Http\Controllers\PLD;

use App\Http\Controllers\Controller;
use App\Http\Requests\PLD\PLDOrderStoreRequest;
use App\Http\Requests\PLD\PLDOrderUpdateRequest;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\SerializationType;
use App\Models\User;
use App\Support\Traits\Controller\DestroysModelRecords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PLDOrderController extends Controller
{
    use DestroysModelRecords;

    // Required for DestroysModelRecords trait
    public static $model = Order::class;

    public function index(Request $request): Response
    {
        $getAllTableHeaders = fn() => $request->user()->collectTranslatedTableHeadersByKey(User::PLD_ORDERS_HEADERS_KEY);
        $getVisibleHeaders = fn() => User::filterOnlyVisibleTableHeaders($getAllTableHeaders());

        return Inertia::render('departments/PLD/pages/orders/Index', [
            // Lazy loads. Refetched only on headers update and locale change
            'allTableHeaders' => $getAllTableHeaders,
            'tableVisibleHeaders' => $getVisibleHeaders,

            // Lazy loads. Never refetched again
            'filterDependencies' => fn() => $this->getFilterDependencies(),
        ]);
    }

    public function create(): Response
    {
        // No lazy loads required, because AJAX request is used on store
        return Inertia::render('departments/PLD/pages/orders/Create', [
            'manufacturers' => Manufacturer::getMinifiedRecordsWithProcessesReadyForOrder(),
            'countriesOrderedByProcessesCount' => Country::orderByProcessesCount()->get(),
            'serializationTypes' => SerializationType::defaultOrdered()->get(),
        ]);
    }

    /**
     * AJAX request
     */
    public function store(PLDOrderStoreRequest $request): JsonResponse
    {
        Order::storeByPLDFromRequest($request);

        return response()->json([
            'success' => true,
        ]);
    }

    public function edit($record): Response
    {
        $record = Order::withBasicPLDRelations()
            ->withBasicPLDRelationCounts()
            ->findOrFail($record);

        $record->appendBasicPLDAttributes();
        $record->append('title'); // Used on generating breadcrumbs

        return Inertia::render('departments/PLD/pages/orders/Edit', [
            // Refetched after record update
            'record' => $record,

            // Lazy loads. Never refetched again
            'manufacturers' => Manufacturer::getMinifiedRecordsWithProcessesReadyForOrder(),
            'countriesOrderedByProcessesCount' => Country::orderByProcessesCount()->get(),
            'currencies' => Currency::orderByName()->get(),
        ]);
    }

    /**
     * AJAX request
     */
    public function update(PLDOrderUpdateRequest $request, Order $record): JsonResponse
    {
        $record->updateByPLDFromRequest($request);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * AJAX request
     */
    public function sentToBDM(Order $record): Order
    {
        $record->sendToBdm();

        // Return refetched updated record
        $record = Order::withBasicPLDRelations()
            ->withBasicPLDRelationCounts()
            ->findOrFail($record->id);

        $record->appendBasicPLDAttributes();

        return $record;
    }

    /**
     * AJAX request
     */
    public function confirm(Order $record): Order
    {
        $record->confirm();

        // Return refetched updated record
        $record = Order::withBasicPLDRelations()
            ->withBasicPLDRelationCounts()
            ->findOrFail($record->id);

        $record->appendBasicPLDAttributes();

        return $record;
    }

    private function getFilterDependencies(): array
    {
        return [
            'manufacturers' => Manufacturer::getMinifiedRecordsWithProcessesReadyForOrder(),
            'countriesOrderedByProcessesCount' => Country::orderByProcessesCount()->get(),
            'bdmUsers' => User::getCMDBDMsMinifed(),
            'statusOptions' => Order::getFilterStatusOptions(),
            'orderNames' => Order::onlyWithName()->orderByName()->pluck('name'),
        ];
    }
}
