<?php

namespace App\Http\Controllers\CMD;

use App\Http\Controllers\Controller;
use App\Http\Requests\CMD\CMDOrderUpdateRequest;
use App\Models\Country;
use App\Models\Currency;
use App\Models\InvoicePaymentType;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CMDOrderController extends Controller
{
    public function index(Request $request): Response
    {
        $getAllTableHeaders = fn() => $request->user()->collectTranslatedTableHeadersByKey(User::CMD_ORDERS_HEADERS_KEY);
        $getVisibleHeaders = fn() => User::filterOnlyVisibleTableHeaders($getAllTableHeaders());

        return Inertia::render('departments/CMD/pages/orders/Index', [
            // Lazy loads. Refetched only on headers update and locale change
            'allTableHeaders' => $getAllTableHeaders,
            'tableVisibleHeaders' => $getVisibleHeaders,

            // Lazy loads. Never refetched again
            'filterDependencies' => fn() => $this->getFilterDependencies(),
            'invoicePaymentTypes' => fn() => InvoicePaymentType::all(), // Used on generating invoice links
        ]);
    }

    public function edit(Order $record): Response
    {
        $record->appendBasicCMDAttributes();
        $record->append('title'); // Used on generating breadcrumbs

        // Load required relations
        $record->load([
            'lastComment',

            'products' => function ($productsQuery) {
                $productsQuery->with([
                    'lastComment',

                    'process' => function ($processQuery) {
                        $processQuery->withRelationsForOrderProduct();
                        // ->withOnlyRequiredSelectsForOrderProduct(); not used because 'agreed_price' also required
                    },
                ]);
            }
        ]);

        // Append additional attributes
        foreach ($record->products as $product) {
            $product->append('production_is_started');
            $product->lastComment?->append('plain_text');
            $product->process->append('full_english_product_label');
        }

        // Return view
        return Inertia::render('departments/CMD/pages/orders/Edit', [
            // Refetched after record update
            'record' => $record,

            // Lazy loads. Never refetched again
            'currencies' => Currency::orderByName()->get(),
            'defaultSelectedCurrencyID' => Currency::getDefaultIdValueForOrders(),
        ]);
    }

    /**
     * AJAX request
     */
    public function update(CMDOrderUpdateRequest $request, Order $record): JsonResponse
    {
        $record->updateByCMDFromRequest($request);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * AJAX request
     */
    public function sentToConfirmation(Order $record): Order
    {
        $record->sendToConfirmation();

        // Return refetched updated record
        $record = Order::withBasicCMDRelations()
            ->withBasicCMDRelationCounts()
            ->findOrFail($record->id);

        $record->appendBasicCMDAttributes();

        return $record;
    }

    /**
     * AJAX request
     */
    public function sentToManufacturer(Order $record): Order
    {
        $record->sendToManufacturer();

        // Return refetched updated record
        $record = Order::withBasicCMDRelations()
            ->withBasicCMDRelationCounts()
            ->findOrFail($record->id);

        $record->appendBasicCMDAttributes();

        return $record;
    }

    /**
     * AJAX request
     */
    public function startProduction(Order $record): Order
    {
        $record->startProduction();

        // Return refetched updated record
        $record = Order::withBasicCMDRelations()
            ->withBasicCMDRelationCounts()
            ->findOrFail($record->id);

        $record->appendBasicCMDAttributes();

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
