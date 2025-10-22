<?php

namespace App\Http\Controllers\MAD;

use App\Http\Controllers\Controller;
use App\Http\Requests\MAD\ManufacturerStoreRequest;
use App\Http\Requests\MAD\ManufacturerUpdateRequest;
use App\Models\Country;
use App\Models\Manufacturer;
use App\Models\ManufacturerBlacklist;
use App\Models\ManufacturerCategory;
use App\Models\ProductClass;
use App\Models\User;
use App\Models\Zone;
use App\Support\FilterDependencies\SimpleFilters\MAD\ManufacturersSimpleFilter;
use App\Support\FilterDependencies\SmartFilters\MAD\ManufacturersSmartFilter;
use App\Support\Helpers\ControllerHelper;
use App\Support\Traits\Controller\DestroysModelRecords;
use App\Support\Traits\Controller\RestoresModelRecords;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MADManufacturerController extends Controller
{
    use DestroysModelRecords;
    use RestoresModelRecords;

    // Required for DestroysModelRecords and RestoresModelRecords traits
    public static $model = Manufacturer::class;

    public function index(Request $request)
    {
        $getAllTableHeaders = fn() => $request->user()->collectTranslatedTableHeadersByKey(User::MAD_EPP_HEADERS_KEY);
        $getVisibleHeaders = fn() => User::filterOnlyVisibleTableHeaders($getAllTableHeaders());

        return Inertia::render('departments/MAD/pages/manufacturers/Index', [
            // Refetched on smart filters change and filter form submit
            'smartFilterDependencies' => ManufacturersSmartFilter::getAllDependencies(),

            // Lazy loads. Refetched only on headers update and locale change
            'allTableHeaders' => $getAllTableHeaders,
            'tableVisibleHeaders' => $getVisibleHeaders,

            // Lazy loads. Never refetched again
            'simpleFilterDependencies' => fn() => ManufacturersSimpleFilter::getAllDependencies(),
        ]);
    }

    public function trash(Request $request)
    {
        $getAllTableHeaders = fn() => ControllerHelper::prependTrashPageTableHeaders(
            $request->user()->collectTranslatedTableHeadersByKey(User::MAD_EPP_HEADERS_KEY)
        );

        $getVisibleHeaders = fn() => User::filterOnlyVisibleTableHeaders($getAllTableHeaders());

        return Inertia::render('departments/MAD/pages/manufacturers/Trash', [
            // Refetched on smart filters change and filter form submit
            'smartFilterDependencies' => ManufacturersSmartFilter::getAllDependencies(),

            // Lazy loads. Refetched only on locale change
            'tableVisibleHeaders' => $getVisibleHeaders,

            // Lazy loads. Never refetched again
            'simpleFilterDependencies' => fn() => ManufacturersSimpleFilter::getAllDependencies(),
        ]);
    }

    public function create()
    {
        // No lazy loads required, because AJAX request is used on store
        return Inertia::render('departments/MAD/pages/manufacturers/Create', [
            'categories' => ManufacturerCategory::orderByName()->get(),
            'productClasses' => ProductClass::orderByName()->get(),
            'analystUsers' => User::getMADAnalystsMinified(),
            'bdmUsers' => User::getCMDBDMsMinifed(),
            'countriesOrderedByName' => Country::orderByName()->get(),
            'zones' => Zone::orderByName()->get(),
            'defaultSelectedZoneIDs' => Zone::getSelectedIDsByDefault(),
            'blacklists' => ManufacturerBlacklist::orderByName()->get(),
        ]);
    }

    /**
     * AJAX request
     */
    public function store(ManufacturerStoreRequest $request)
    {
        Manufacturer::storeByMADFromRequest($request);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Route model binding is not used, because trashed records can also be edited
     */
    public function edit($record)
    {
        $fetchedRecord = Manufacturer::withTrashed()
            ->withBasicRelations()
            ->findOrFail($record);

        $fetchedRecord->appendBasicAttributes();
        $fetchedRecord->append('title'); // Used on generating breadcrumbs

        return Inertia::render('departments/MAD/pages/manufacturers/Edit', [
            // Refetched after record update
            'record' => $fetchedRecord,
            'breadcrumbs' => $fetchedRecord->generateBreadcrumbs('MAD'),

            // Lazy loads. Never refetched again
            'categories' => fn() => ManufacturerCategory::orderByName()->get(),
            'productClasses' => fn() => ProductClass::orderByName()->get(),
            'analystUsers' => fn() => User::getMADAnalystsMinified(),
            'bdmUsers' => fn() => User::getCMDBDMsMinifed(),
            'countriesOrderedByName' => fn() => Country::orderByName()->get(),
            'zones' => fn() => Zone::orderByName()->get(),
            'blacklists' => fn() => ManufacturerBlacklist::orderByName()->get(),
        ]);
    }

    /**
     * AJAX request
     *
     * Route model binding is not used, because trashed records can also be edited
     */
    public function update(ManufacturerUpdateRequest $request, $record)
    {
        $fetchedRecord = Manufacturer::withTrashed()->findOrFail($record);
        $fetchedRecord->updateByMADFromRequest($request);

        return response()->json([
            'success' => true,
        ]);
    }
}
