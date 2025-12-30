<?php

namespace App\Http\Controllers\CMD;

use App\Http\Controllers\Controller;
use App\Http\Requests\CMD\CMDOrderUpdateRequest;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CMDOrderController extends Controller
{
    public function index(Request $request)
    {
        $getAllTableHeaders = fn() => $request->user()->collectTranslatedTableHeadersByKey(User::CMD_ORDERS_HEADERS_KEY);
        $getVisibleHeaders = fn() => User::filterOnlyVisibleTableHeaders($getAllTableHeaders());

        return Inertia::render('departments/CMD/pages/orders/Index', [
            // Lazy loads. Refetched only on headers update and locale change
            'allTableHeaders' => $getAllTableHeaders,
            'tableVisibleHeaders' => $getVisibleHeaders,

            // Lazy loads. Never refetched again
            'filterDependencies' => fn() => $this->getfilterDependencies(),
        ]);
    }

    public function edit(Order $record)
    {
        $record->appendBasicAttributes();
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
    public function update(CMDOrderUpdateRequest $request, Order $record)
    {
        $record->updateByCMDFromRequest($request);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * AJAX request
     */
    public function sentToConfirmation(Order $record)
    {
        $record->sendToConfirmation();

        // Return refetched updated record
        $record = Order::withBasicRelations()
            ->withBasicRelationCounts()
            ->findOrFail($record->id);

        $record->appendBasicAttributes();

        return $record;
    }

    private function getfilterDependencies(): array
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
