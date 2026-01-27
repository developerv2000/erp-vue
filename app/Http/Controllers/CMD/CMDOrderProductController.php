<?php

namespace App\Http\Controllers\CMD;

use App\Http\Controllers\Controller;
use App\Http\Requests\CMD\CMDOrderProductUpdateRequest;
use App\Models\Country;
use App\Models\Manufacturer;
use App\Models\MarketingAuthorizationHolder;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Process;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CMDOrderProductController extends Controller
{
    public function index(Request $request): Response
    {
        $getAllTableHeaders = fn() => $request->user()->collectTranslatedTableHeadersByKey(User::CMD_ORDER_PRODUCTS_HEADERS_KEY);
        $getVisibleHeaders = fn() => User::filterOnlyVisibleTableHeaders($getAllTableHeaders());

        return Inertia::render('departments/CMD/pages/order-products/Index', [
            // Lazy loads. Refetched only on headers update and locale change
            'allTableHeaders' => $getAllTableHeaders,
            'tableVisibleHeaders' => $getVisibleHeaders,

            // Lazy loads. Never refetched again
            'filterDependencies' => fn() => $this->getFilterDependencies(),
        ]);
    }

    public function edit($record): Response
    {
        // Fetch record with required relations
        $record = OrderProduct::withBasicCMDRelations()
            ->withBasicCMDRelationCounts()
            ->findorfail($record);

        // Secure action
        $this->authorizeOrderProductEdit($record);

        // Append additional attributes
        $record->appendBasicCMDAttributes();
        $record->append('can_be_prepared_for_shipping_from_manufacturer');
        $record->append('title'); // Used on generating breadcrumbs

        // Return response
        return Inertia::render('departments/CMD/pages/order-products/Edit', [
            // Refetched after record update
            'record' => $record,
        ]);
    }

    /**
     * AJAX request
     */
    public function update(CMDOrderProductUpdateRequest $request,  OrderProduct $record): JsonResponse
    {
        // Secure action
        $this->authorizeOrderProductEdit($record);

        // Update record
        $record->updateByCMDFromRequest($request);

        // Return response
        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * AJAX request
     */
    public function endProduction(OrderProduct $record): OrderProduct
    {
        // Secure action
        $this->authorizeOrderProductEdit($record);

        // End production
        $record->endProduction();

        // Return refetched updated record
        $record = OrderProduct::withBasicCMDRelations()
            ->withBasicCMDRelationCounts()
            ->findOrFail($record->id);

        $record->appendBasicCMDAttributes();

        return $record;
    }

    /**
     * AJAX request
     */
    public function setAsReadyForShipmentFromManufacturer(OrderProduct $record): OrderProduct
    {
        // Secure action
        $this->authorizeOrderProductEdit($record);

        // Set as ready for shipment from manufacturer
        $record->setAsReadyForShipmentFromManufacturer();

        // Return refetched updated record
        $record = OrderProduct::withBasicCMDRelations()
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
            'MAHs' => MarketingAuthorizationHolder::orderByName()->get(),
            'enTrademarks' => Process::pluckAllEnTrademarks(),
            'ruTrademarks' => Process::pluckAllRuTrademarks(),
            'orderNames' => Order::onlyWithName()->orderByName()->pluck('name'),
        ];
    }

    private function authorizeOrderProductEdit(OrderProduct $product): void
    {
        Gate::authorize('edit-CMD-order-product', $product);
    }
}
