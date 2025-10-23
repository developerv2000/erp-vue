<?php

namespace App\Http\Controllers\MAD;

use App\Http\Controllers\Controller;
use App\Http\Requests\MAD\ProcessStoreRequest;
use App\Http\Requests\MAD\ProcessUpdateRequest;
use App\Models\Country;
use App\Models\Currency;
use App\Models\MarketingAuthorizationHolder;
use App\Models\Process;
use App\Models\ProcessResponsiblePerson;
use App\Models\ProcessStatus;
use App\Models\Product;
use App\Models\ProductClass;
use App\Models\ProductForm;
use App\Models\ProductShelfLife;
use App\Models\User;
use App\Support\FilterDependencies\SimpleFilters\MAD\ProcessesSimpleFilter;
use App\Support\FilterDependencies\SmartFilters\MAD\ProcessesSmartFilter;
use App\Support\Helpers\ControllerHelper;
use App\Support\Traits\Controller\DestroysModelRecords;
use App\Support\Traits\Controller\RestoresModelRecords;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class MADProcessController extends Controller
{
    use DestroysModelRecords;
    use RestoresModelRecords;

    // Required for DestroysModelRecords and RestoresModelRecords traits
    public static $model = Process::class;

    public function index(Request $request)
    {
        $getAllTableHeaders = fn() => $request->user()->collectTranslatedTableHeadersByKey(User::MAD_VPS_HEADERS_KEY);
        $getVisibleHeaders = fn() => User::filterOnlyVisibleTableHeaders($getAllTableHeaders());

        return Inertia::render('departments/MAD/pages/processes/Index', [
            // Refetched on smart filters change and filter form submit
            'smartFilterDependencies' => ProcessesSmartFilter::getAllDependencies(),

            // Lazy loads. Refetched only on headers update and locale change
            'allTableHeaders' => $getAllTableHeaders,
            'tableVisibleHeaders' => $getVisibleHeaders,

            // Lazy loads. Never refetched again
            'simpleFilterDependencies' => fn() => ProcessesSimpleFilter::getAllDependencies(),
        ]);
    }

    public function trash(Request $request)
    {
        $getAllTableHeaders = fn() => ControllerHelper::prependTrashPageTableHeaders(
            $request->user()->collectTranslatedTableHeadersByKey(User::MAD_VPS_HEADERS_KEY)
        );

        $getVisibleHeaders = fn() => User::filterOnlyVisibleTableHeaders($getAllTableHeaders());

        return Inertia::render('departments/MAD/pages/processes/Trash', [
            // Refetched on smart filters change and filter form submit
            'smartFilterDependencies' => ProcessesSmartFilter::getAllDependencies(),

            // Lazy loads. Refetched only on locale change
            'tableVisibleHeaders' => $getVisibleHeaders,

            // Lazy loads. Never refetched again
            'simpleFilterDependencies' => fn() => ProcessesSimpleFilter::getAllDependencies(),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('departments/MAD/pages/processes/Create', [
            // Refetched after creating without redirect
            'product' => Product::withBasicRelations()->findOrFail($request->input('product_id')),

            // Lazy loads. Never refetched again
            'restrictedStatuses' => fn() => ProcessStatus::getAllRestrictedByPermissions()->load('generalStatus'), // IMPORTANT
            'productForms' => fn() => ProductForm::getMinifiedRecordsWithName(),
            'shelfLifes' => fn() => ProductShelfLife::all(),
            'productClasses' => fn() => ProductClass::orderByName()->get(),
            'countriesOrderedByProcessesCount' => fn() => Country::orderByProcessesCount()->get(),
            'responsiblePeople' => fn() => ProcessResponsiblePerson::orderByName()->get(),
            'countriesOrderedByName' => fn() => Country::orderByName()->get(),
            'currencies' => fn() => Currency::orderByName()->get(),
            'MAHs' => fn() => MarketingAuthorizationHolder::orderByName()->get(),
            'defaultSelectedStatusID' => fn() => ProcessStatus::getSelectedIDByDefault(),
            'defaultSelectedMAHID' => fn() => MarketingAuthorizationHolder::getDefaultSelectedIDValue(),
            'defaultSelectedCurrencyID' => fn() => Currency::getDefaultIdValueForMADProcesses(),
        ]);
    }

    /**
     * AJAX request
     */
    public function store(ProcessStoreRequest $request)
    {
        // Store multiple records
        Process::storeMultipleRecordsByMADFromRequest($request);

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
        $fetchedRecord = Process::withTrashed()
            ->withBasicRelations()
            ->findOrFail($record);

        $fetchedRecord->appendBasicAttributes();
        $fetchedRecord->append('title'); // Used on generating breadcrumbs

        return Inertia::render('departments/MAD/pages/processes/Edit', [
            // Refetched after record update
            'record' => $fetchedRecord,
            'breadcrumbs' => $fetchedRecord->generateBreadcrumbs('MAD'),

            // Lazy loads. Never refetched again
            'countriesOrderedByProcessesCount' => fn() => Country::orderByProcessesCount()->get(),
            'responsiblePeople' => fn() => ProcessResponsiblePerson::orderByName()->get(),
            'defaultSelectedStatusIDs' => fn() => ProcessStatus::getDefaultSelectedIDValue(),
            'countriesOrderedByName' => fn() => Country::orderByName()->get(),
            'currencies' => fn() => Currency::orderByName()->get(),
            'MAHs' => fn() => MarketingAuthorizationHolder::orderByName()->get(),
            'defaultSelectedMAHID' => fn() => MarketingAuthorizationHolder::getDefaultSelectedIDValue(),
            'defaultSelectedCurrencyID' => fn() => Currency::getDefaultIdValueForMADProcesses(),
        ]);
    }

    /**
     * AJAX request
     *
     * Route model binding is not used, because trashed records can also be edited
     */
    public function update(ProcessUpdateRequest $request, $record)
    {
        $fetchedRecord = Process::withTrashed()->findOrFail($record);
        $fetchedRecord->updateByMADFromRequest($request);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * AJAX request
     */
    public function updateContractedInAspValue(Request $request)
    {
        $record = Process::withTrashed()
            ->withBasicRelations()
            ->withBasicRelationCounts()
            ->findOrFail($request->input('id'));

        // Return error if record isn`t ready for ASP contract
        if (!$record->is_ready_for_asp_contract) {
            abort(403);
        }

        // Update record
        $record->update([
            'contracted_in_asp' => $request->input('new_value'),
        ]);

        // Append basic attributes and return record
        $record->appendBasicAttributes();
        return $record;
    }

    /**
     * AJAX request
     */
    public function updateRegisteredInAspValue(Request $request)
    {
        $record = Process::withTrashed()
            ->withBasicRelations()
            ->withBasicRelationCounts()
            ->findOrFail($request->input('id'));

        // Return error if record isn`t ready for ASP registration
        if (!$record->is_ready_for_asp_registration) {
            abort(403);
        }

        // Update record
        $record->update([
            'registered_in_asp' => $request->input('new_value'),
        ]);

        // Append basic attributes and return record
        $record->appendBasicAttributes();
        return $record;
    }

    /**
     * AJAX request
     */
    public function updateReadyForOrderValue(Request $request)
    {
        $record = Process::withTrashed()
            ->withBasicRelations()
            ->withBasicRelationCounts()
            ->findOrFail($request->input('id'));

        // Return error if record isn`t ready for ASP contract
        if (!$record->can_be_marked_as_ready_for_order) {
            abort(403);
        }

        // Update record
        $isReady = $request->input('is_ready');

        $record->update([
            'readiness_for_order_date' => $isReady ? now() : null,
        ]);

        // Append basic attributes and return record
        $record->appendBasicAttributes();
        return $record;
    }
}
