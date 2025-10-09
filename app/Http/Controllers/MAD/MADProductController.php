<?php

namespace App\Http\Controllers\MAD;

use App\Http\Controllers\Controller;
use App\Http\Requests\MAD\ProductUpdateRequest;
use App\Models\Country;
use App\Models\Inn;
use App\Models\Manufacturer;
use App\Models\ManufacturerCategory;
use App\Models\Product;
use App\Models\ProductClass;
use App\Models\ProductForm;
use App\Models\ProductShelfLife;
use App\Models\User;
use App\Support\FilterDependencies\SimpleFilters\MAD\ProductsSimpleFilterDependencies;
use App\Support\FilterDependencies\SmartFilters\MAD\ProductsSmartFilterDependencies;
use App\Support\Helpers\ControllerHelper;
use App\Support\Traits\Controller\DestroysModelRecords;
use App\Support\Traits\Controller\RestoresModelRecords;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MADProductController extends Controller
{
    use DestroysModelRecords;
    use RestoresModelRecords;

    // Required for DestroysModelRecords and RestoresModelRecords traits
    public static $model = Product::class;

    public function index(Request $request)
    {
        $getAllTableHeaders = fn() => $request->user()->collectTranslatedTableHeadersByKey(User::MAD_IVP_HEADERS_KEY);
        $getVisibleHeaders = fn() => User::filterOnlyVisibleTableHeaders($getAllTableHeaders());

        return Inertia::render('departments/MAD/pages/products/Index', [
            // Refetched on smart filters change and filter form submit
            'smartFilterDependencies' => ProductsSmartFilterDependencies::getAllDependencies(),

            // Lazy loads
            'simpleFilterDependencies' => fn() => ProductsSimpleFilterDependencies::getAllDependencies(),
            'allTableHeaders' => $getAllTableHeaders, // Refetched only on headers update
            'tableVisibleHeaders' => $getVisibleHeaders, // Refetched only on headers update
        ]);
    }

    public function trash(Request $request)
    {
        $getAllTableHeaders = fn() => ControllerHelper::prependTrashPageTableHeaders(
            $request->user()->collectTranslatedTableHeadersByKey(User::MAD_IVP_HEADERS_KEY)
        );

        $getVisibleHeaders = fn() => User::filterOnlyVisibleTableHeaders($getAllTableHeaders());

        return Inertia::render('departments/MAD/pages/products/Trash', [
            // Refetched on smart filters change and filter form submit
            'smartFilterDependencies' => ProductsSmartFilterDependencies::getAllDependencies(),

            // Lazy loads, never refetched again
            'simpleFilterDependencies' => fn() => ProductsSimpleFilterDependencies::getAllDependencies(),
            'tableVisibleHeaders' => $getVisibleHeaders,
        ]);
    }

    public function create()
    {
        // No lazy loads required, because AJAX request is used on store
        return Inertia::render('departments/MAD/pages/products/Create', [
            'manufacturers' => Manufacturer::getMinifiedRecordsWithName(),
            'analystUsers' => User::getMADAnalystsMinified(),
            'bdmUsers' => User::getCMDBDMsMinifed(),
            'productClasses' => ProductClass::orderByName()->get(),
            'productForms' => ProductForm::getMinifiedRecordsWithName(),
            'shelfLifes' => ProductShelfLife::all(),
            'zones' => Zone::orderByName()->get(),
            'inns' => Inn::orderByName()->get(),
            'countriesOrderedByName' => Country::orderByName()->get(),
            'manufacturerCategories' => ManufacturerCategory::orderByName()->get(),
            'defaultSelectedClassID' => ProductClass::getDefaultSelectedIDValue(),
            'defaultSelectedShelfLifeID' => ProductShelfLife::getDefaultSelectedIDValue(),
            'defaultSelectedZoneIDs' => Zone::getRelatedDefaultSelectedIDValues(),
        ]);
    }

    /**
     * AJAX request
     */
    public function store($request)
    {
        Product::storeMultipleRecordsByMADFromRequest($request);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Route model binding is not used, because trashed records can also be edited
     */
    public function edit($record)
    {
        $fetchedRecord = Product::withTrashed()
            ->withBasicRelations()
            ->findOrFail($record);

        $fetchedRecord->appendBasicAttributes();
        $fetchedRecord->append('title'); // Used on generating breadcrumbs

        return Inertia::render('departments/MAD/pages/products/Edit', [
            // Refetched after record update
            'record' => $fetchedRecord,
            'breadcrumbs' => $fetchedRecord->generateBreadcrumbs('mad'),

            // Lazy loads, never refetched again
            'manufacturers' => Manufacturer::getMinifiedRecordsWithName(),
            'analystUsers' => User::getMADAnalystsMinified(),
            'bdmUsers' => User::getCMDBDMsMinifed(),
            'productClasses' => ProductClass::orderByName()->get(),
            'productForms' => ProductForm::getMinifiedRecordsWithName(),
            'shelfLifes' => ProductShelfLife::all(),
            'zones' => Zone::orderByName()->get(),
            'inns' => Inn::orderByName()->get(),
            'countriesOrderedByName' => Country::orderByName()->get(),
            'manufacturerCategories' => ManufacturerCategory::orderByName()->get(),
        ]);
    }

    /**
     * AJAX request
     *
     * Route model binding is not used, because trashed records can also be edited
     */
    public function update(ProductUpdateRequest $request, $record)
    {
        $fetchedRecord = Product::withTrashed()->findOrFail($record);
        $fetchedRecord->updateByMADFromRequest($request);

        return response()->json([
            'success' => true,
        ]);
    }
}
