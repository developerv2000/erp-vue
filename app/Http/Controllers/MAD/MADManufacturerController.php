<?php

namespace App\Http\Controllers\MAD;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManufacturerStoreRequest;
use App\Http\Requests\ManufacturerUpdateRequest;
use App\Models\Manufacturer;
use App\Models\User;
use App\Support\FilterDependencies\SimpleFilters\MAD\ManufacturersSimpleFilterDependencies;
use App\Support\FilterDependencies\SmartFilters\MAD\ManufacturersSmartFilterDependencies;
use App\Support\Traits\Controller\DestroysModelRecords;
use App\Support\Traits\Controller\RestoresModelRecords;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MADManufacturerController extends Controller
{
    use DestroysModelRecords;
    use RestoresModelRecords;

    // used in multiple destroy/restore traits
    public static $model = Manufacturer::class;

    public function index(Request $request)
    {
        $getAllTableHeaders = fn() => $request->user()->collectTableHeadersBySettingsKey(User::SETTINGS_KEY_OF_MAD_EPP_TABLE);
        $getVisibleHeaders = fn() => User::filterOnlyVisibleHeaders($getAllTableHeaders());

        return Inertia::render('departments/MAD/pages/manufacturers/Index', [
            'allTableHeaders' => $getAllTableHeaders, // Lazy load
            'tableVisibleHeaders' => $getVisibleHeaders, // Lazy load
            'simpleFilterDependencies' => fn() => ManufacturersSimpleFilterDependencies::getAllDependencies(), // Lazy load
            'smartFilterDependencies' => ManufacturersSmartFilterDependencies::getAllDependencies(),
        ]);
    }

    public function trash(Request $request)
    {
        $getAllTableHeaders = fn() => $request->user()->collectTableHeadersBySettingsKey(User::SETTINGS_KEY_OF_MAD_EPP_TABLE);
        $getVisibleHeaders = fn() => User::filterOnlyVisibleHeaders($getAllTableHeaders());

        return Inertia::render('departments/MAD/pages/manufacturers/Trash', [
            'allTableHeaders' => $getAllTableHeaders, // Lazy load
            'tableVisibleHeaders' => $getVisibleHeaders, // Lazy load
            'simpleFilterDependencies' => fn() => ManufacturersSimpleFilterDependencies::getAllDependencies(), // Lazy load
            'smartFilterDependencies' => ManufacturersSmartFilterDependencies::getAllDependencies(),
        ]);
    }

    public function create()
    {
        return view('MAD.manufacturers.create');
    }

    public function store(ManufacturerStoreRequest $request)
    {
        Manufacturer::createFromRequest($request);

        return to_route('mad.manufacturers.index');
    }

    /**
     * Route model binding is not used, because trashed records can also be edited.
     * Route model binding looks only for untrashed records!
     */
    public function edit(Request $request, $record)
    {
        $record = Manufacturer::withTrashed()->findOrFail($record);

        return view('MAD.manufacturers.edit', compact('record'));
    }

    /**
     * Route model binding is not used, because trashed records can also be edited.
     * Route model binding looks only for untrashed records!
     */
    public function update(ManufacturerUpdateRequest $request, $record)
    {
        $record = Manufacturer::withTrashed()->findOrFail($record);
        $record->updateFromRequest($request);

        return redirect($request->input('previous_url'));
    }

    public function exportAsExcel(Request $request)
    {
        // Preapare request for valid model querying
        Manufacturer::addRefererQueryParamsToRequest($request);
        Manufacturer::addDefaultQueryParamsToRequest($request);

        // Get finalized records query
        $query = Manufacturer::withRelationsForExport();
        $filteredQuery = Manufacturer::filterQueryForRequest($query, $request);
        $records = Manufacturer::finalizeQueryForRequest($filteredQuery, $request, 'query');

        // Export records
        return Manufacturer::exportRecordsAsExcel($records);
    }
}
