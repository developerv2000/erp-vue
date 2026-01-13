<?php

namespace App\Http\Controllers\MD;

use App\Http\Controllers\Controller;
use App\Http\Requests\MD\MDSerializedByManufacturerUpdateRequest;
use App\Models\Country;
use App\Models\Manufacturer;
use App\Models\OrderProduct;
use App\Models\Process;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MDSerializedByManufacturerController extends Controller
{
    public function index(Request $request): Response
    {
        $getAllTableHeaders = fn() => $request->user()
            ->collectTranslatedTableHeadersByKey(User::MD_SERIALIZED_BY_MANUFACTURER_HEADERS_KEY);

        $getVisibleHeaders = fn() => User::filterOnlyVisibleTableHeaders($getAllTableHeaders());

        return Inertia::render('departments/MD/pages/serialized-by-manufacturer/Index', [
            // Lazy loads. Refetched only on headers update and locale change
            'allTableHeaders' => $getAllTableHeaders,
            'tableVisibleHeaders' => $getVisibleHeaders,

            // Lazy loads. Never refetched again
            'filterDependencies' => fn() => $this->getFilterDependencies(),
        ]);
    }

    public function edit($record): Response
    {
        $record = OrderProduct::withBasicMDRelations()
            ->withBasicMDRelationCounts()
            ->findorfail($record);

        $record->appendBasicMDAttributes();
        $record->append('title'); // Used on generating breadcrumbs

        return Inertia::render('departments/MD/pages/serialized-by-manufacturer/Edit', [
            // Refetched after record update
            'record' => $record,
        ]);
    }

    /**
     * AJAX request
     */
    public function update(MDSerializedByManufacturerUpdateRequest $request, OrderProduct $record): JsonResponse
    {
        $record->updateSerializedByManufacturerByMDFromRequest($request);

        return response()->json([
            'success' => true,
        ]);
    }

    private function getFilterDependencies(): array
    {
        return [
            'manufacturers' => Manufacturer::getMinifiedRecordsWithProcessesReadyForOrder(),
            'countriesOrderedByProcessesCount' => Country::orderByProcessesCount()->get(),
            'enTrademarks' => Process::pluckAllEnTrademarks(),
            'ruTrademarks' => Process::pluckAllRuTrademarks(),
        ];
    }
}
