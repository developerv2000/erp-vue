<?php

namespace App\Http\Controllers\MAD;

use App\Http\Controllers\Controller;
use App\Http\Requests\MAD\ProductUpdateRequest;
use App\Models\Atx;
use App\Models\Country;
use App\Models\Inn;
use App\Models\Manufacturer;
use App\Models\ManufacturerCategory;
use App\Models\Product;
use App\Models\ProductClass;
use App\Models\ProductForm;
use App\Models\ProductShelfLife;
use App\Models\User;
use App\Models\Zone;
use App\Support\Helpers\ControllerHelper;
use App\Support\SmartFilters\MAD\ProductsSmartFilter;
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
            'smartFilterDependencies' => ProductsSmartFilter::getAllDependencies(),

            // Lazy loads. Refetched only on headers update and locale change
            'allTableHeaders' => $getAllTableHeaders,
            'tableVisibleHeaders' => $getVisibleHeaders,

            // Lazy loads. Never refetched again
            'simpleFilterDependencies' => fn() => $this->getSimpleFilterDependencies(),
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
            'smartFilterDependencies' => ProductsSmartFilter::getAllDependencies(),

            // Lazy loads. Refetched only on locale change
            'tableVisibleHeaders' => $getVisibleHeaders,

            // Lazy loads. Never refetched again
            'simpleFilterDependencies' => fn() => $this->getSimpleFilterDependencies(),
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
            'defaultSelectedClassID' => ProductClass::getSelectedIDByDefault(),
            'defaultSelectedShelfLifeID' => ProductShelfLife::getSelectedIDByDefault(),
            'defaultSelectedZoneIDs' => Zone::getSelectedIDsByDefault(),
        ]);
    }

    /**
     * Get similar records based on the provided request data.
     *
     * Used on AJAX requests to retrieve similar records, on the products create form.
     */
    public function getSimilarRecordsForRequest(Request $request)
    {
        return Product::getSimilarRecordsForRequest($request);
    }

    /**
     * AJAX request on products.create
     */
    public function getMatchedATXForRequest(Request $request)
    {
        $atx = Atx::where('inn_id', $request->input('inn_id'))
            ->where('form_id', $request->input('form_id'))
            ->first();

        return $atx;
    }

    /**
     * AJAX request
     */
    public function store(Request $request)
    {
        // Sync ATX
        $atx = Atx::syncAtxWithProductOnProductStoreOrUpdate($request);

        // Store multiple records
        Product::storeMultipleRecordsByMADFromRequest($request, $atx);

        // Return success response
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

        // Make sure that ATX exists for this product
        $fetchedRecord->ensureAtxExists();

        return Inertia::render('departments/MAD/pages/products/Edit', [
            // Refetched after record update
            'record' => $fetchedRecord,
            'breadcrumbs' => $fetchedRecord->generateBreadcrumbs('MAD'),

            // Lazy loads. Never refetched again
            'manufacturers' => fn() => Manufacturer::getMinifiedRecordsWithName(),
            'analystUsers' => fn() => User::getMADAnalystsMinified(),
            'bdmUsers' => fn() => User::getCMDBDMsMinifed(),
            'productClasses' => fn() => ProductClass::orderByName()->get(),
            'productForms' => fn() => ProductForm::getMinifiedRecordsWithName(),
            'shelfLifes' => fn() => ProductShelfLife::all(),
            'zones' => fn() => Zone::orderByName()->get(),
            'inns' => fn() => Inn::orderByName()->get(),
            'countriesOrderedByName' => fn() => Country::orderByName()->get(),
            'manufacturerCategories' => fn() => ManufacturerCategory::orderByName()->get(),
        ]);
    }

    /**
     * AJAX request
     *
     * Route model binding is not used, because trashed records can also be edited
     */
    public function update(ProductUpdateRequest $request, $record)
    {
        // Sync ATX
        $atx = Atx::syncAtxWithProductOnProductStoreOrUpdate($request);

        // Update record
        $fetchedRecord = Product::withTrashed()->findOrFail($record);
        $fetchedRecord->updateByMADFromRequest($request, $atx);

        return response()->json([
            'success' => true,
        ]);
    }

    private function getSimpleFilterDependencies(): array
    {
        return [
            'analystUsers' => User::getMADAnalystsMinified(),
            'bdmUsers' => User::getCMDBDMsMinifed(),
            'productClasses' => ProductClass::orderByName()->get(),
            'shelfLifes' => ProductShelfLife::all(),
            'zones' => Zone::orderByName()->get(),
            'countriesOrderedByName' => Country::orderByName()->get(),
            'manufacturerCategories' => ManufacturerCategory::orderByName()->get(),
            'brands' => Product::getAllUniqueBrands(),
        ];
    }
}
