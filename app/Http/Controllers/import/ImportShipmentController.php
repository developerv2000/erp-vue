<?php

namespace App\Http\Controllers\import;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportShipmentStoreRequest;
use App\Http\Requests\ImportShipmentUpdateRequest;
use App\Models\Currency;
use App\Models\Manufacturer;
use App\Models\Shipment;
use App\Models\ShipmentDestination;
use App\Models\TransportationMethod;
use App\Models\User;
use App\Support\Traits\Controller\DestroysModelRecords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ImportShipmentController extends Controller
{
    use DestroysModelRecords;

    // Required for DestroysModelRecords trait
    public static $model = Shipment::class;

    public function index(Request $request): Response
    {
        $getAllTableHeaders = fn() => $request->user()->collectTranslatedTableHeadersByKey(User::IMPORT_SHIPMENTS_HEADERS_KEY);
        $getVisibleHeaders = fn() => User::filterOnlyVisibleTableHeaders($getAllTableHeaders());

        return Inertia::render('sections/import/pages/shipments/Index', [
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
        return Inertia::render('sections/import/pages/shipments/Create', [
            'manufacturers' => Manufacturer::getMinifiedRecordsWithProcessesReadyForOrder(),
            'transportationMethods' => TransportationMethod::all(),
            'shipmentDestinations' => ShipmentDestination::all(),
            'currencies' => Currency::orderByName()->get(),
        ]);
    }

    /**
     * AJAX request
     */
    public function store(ImportShipmentStoreRequest $request): JsonResponse
    {
        Shipment::storeFromImportPageRequest($request);

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

        return Inertia::render('sections/import/pages/shipments/Edit', [
            // Refetched after record update
            'record' => $record,

            // Lazy loads. Never refetched again
            'transportationMethods' => TransportationMethod::all(),
            'shipmentDestinations' => ShipmentDestination::all(),
            'currencies' => Currency::orderByName()->get(),
        ]);
    }

    /**
     * AJAX request
     */
    public function update(ImportShipmentUpdateRequest $request, Shipment $record): JsonResponse
    {
        $record->updateFromImportPageRequest($request);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * AJAX request
     */
    public function complete(Shipment $record): Shipment
    {
        $record->complete();

        // Return refetched updated record
        $record = Shipment::withBasicImportDRelations()
            ->withBasicImportRelationCounts()
            ->findOrFail($record->id);

        $record->appendBasicImportAttributes();

        return $record;
    }

    /**
     * AJAX request
     */
    public function arriveAtWarehouse(Shipment $record): Shipment
    {
        $record->arriveAtWarehouse();

        // Return refetched updated record
        $record = Shipment::withBasicImportDRelations()
            ->withBasicImportRelationCounts()
            ->findOrFail($record->id);

        $record->appendBasicImportAttributes();

        return $record;
    }

    private function getFilterDependencies(): array
    {
        return [
            'manufacturers' => Manufacturer::getMinifiedRecordsWithProcessesReadyForOrder(),
            'transportationMethods' => TransportationMethod::all(),
            'shipmentDestinations' => ShipmentDestination::all(),
        ];
    }
}
