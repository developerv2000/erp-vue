<?php

namespace App\Http\Controllers\MAD;

use App\Http\Controllers\Controller;
use App\Http\Requests\MAD\ProcessStoreRequest;
use App\Http\Requests\MAD\ProcessUpdateRequest;
use App\Models\Country;
use App\Models\Currency;
use App\Models\ManufacturerCategory;
use App\Models\MarketingAuthorizationHolder;
use App\Models\Process;
use App\Models\ProcessGeneralStatus;
use App\Models\ProcessResponsiblePerson;
use App\Models\ProcessStatus;
use App\Models\Product;
use App\Models\ProductClass;
use App\Models\ProductForm;
use App\Models\ProductShelfLife;
use App\Models\User;
use App\Notifications\ProcessMarkedAsReadyForOrder;
use App\Support\Helpers\ControllerHelper;
use App\Support\SmartFilters\MAD\ProcessesSmartFilter;
use App\Support\Traits\Controller\DestroysModelRecords;
use App\Support\Traits\Controller\RestoresModelRecords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class MADProcessController extends Controller
{
    use DestroysModelRecords;
    use RestoresModelRecords;

    // Required for DestroysModelRecords and RestoresModelRecords traits
    public static $model = Process::class;

    public function index(Request $request): Response
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
            'simpleFilterDependencies' => fn() => $this->getSimpleFilterDependencies(),
        ]);
    }

    public function trash(Request $request): Response
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
            'simpleFilterDependencies' => fn() => $this->getSimpleFilterDependencies(),
        ]);
    }

    public function create(Request $request): Response
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
     * Route model binding is not used, because trashed records can also be duplicated
     *
     * AJAX "mad.processes.store" route is also used for duplication, almost the same as creation!
     */
    public function duplicate(Request $request, $record): Response
    {
        // Secure route
        $fetchedRecord = Process::withBasicRelations()->findOrFail($record);
        $fetchedRecord->ensureAuthUserHasAccessToProcess($request);

        return Inertia::render('departments/MAD/pages/processes/Duplicate', [
            // Never refetched again
            'record' => $fetchedRecord,

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
            'defaultSelectedMAHID' => fn() => MarketingAuthorizationHolder::getDefaultSelectedIDValue(),
            'defaultSelectedCurrencyID' => fn() => Currency::getDefaultIdValueForMADProcesses(),
        ]);
    }

    /**
     * AJAX request
     */
    public function store(ProcessStoreRequest $request): JsonResponse
    {
        DB::transaction(function () use ($request) {
            // Validate related product uniqueness and sync updates
            $product = Product::findOrFail($request->input('product_id'));
            $request->validateProductUniqueness($product);
            $product->syncOnRelatedProcessCreateOrEdit($request);

            // Store one or multiple processes
            Process::storeMultipleRecordsByMADFromRequest($request);
        });

         // Transaction committed successfully
        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Route model binding is not used, because trashed records can also be edited
     */
    public function edit($record): Response
    {
        $fetchedRecord = Process::withTrashed()
            ->withBasicRelations()
            ->findOrFail($record);

        $fetchedRecord->appendBasicAttributes();
        $fetchedRecord->append([
            'title', // Used on generating breadcrumbs
            'current_status_can_be_edited_for_auth_user' // Used on "status_id" field
        ]);

        return Inertia::render('departments/MAD/pages/processes/Edit', [
            // Refetched after record update
            'record' => $fetchedRecord,
            'breadcrumbs' => $fetchedRecord->generateBreadcrumbs('MAD'),

            // Lazy loads. Never refetched again
            'restrictedStatuses' => fn() => ProcessStatus::getAllRestrictedByPermissions() // IMPORTANT
                ->load('generalStatus')
                ->append('is_stopped_status'),

            // Used to display status name, when current status name misses in "restrictedStatuses"
            'allStatuses' => fn() => ProcessStatus::all(),

            'productForms' => fn() => ProductForm::getMinifiedRecordsWithName(),
            'shelfLifes' => fn() => ProductShelfLife::all(),
            'productClasses' => fn() => ProductClass::orderByName()->get(),
            'countriesOrderedByProcessesCount' => fn() => Country::orderByProcessesCount()->get(),
            'responsiblePeople' => fn() => ProcessResponsiblePerson::orderByName()->get(),
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
    public function update(ProcessUpdateRequest $request, $record): JsonResponse
    {
        DB::transaction(function () use ($request, $record) {
            // Validate related product uniqueness and sync updates
            $product = Product::findOrFail($request->input('product_id'));
            $request->validateProductUniqueness($product);
            $product->syncOnRelatedProcessCreateOrEdit($request);

            // Update record
            $fetchedRecord = Process::withTrashed()->findOrFail($record);
            $fetchedRecord->updateByMADFromRequest($request);
        });

         // Transaction committed successfully
        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * AJAX request
     */
    public function updateContractedInAspValue(Request $request): Process
    {
        $record = Process::withTrashed()
            ->with('status.generalStatus') // Required for 'is_ready_for_asp_contract' attribute
            ->findOrFail($request->input('id'));

        // Return error if record isn`t ready for ASP contract
        if (!$record->is_ready_for_asp_contract) {
            abort(403);
        }

        // Update record
        $record->update([
            'contracted_in_asp' => $request->boolean('new_value'),
        ]);

        // Refetch record because relations lost after update() call
        $record = Process::withTrashed()
            ->withBasicRelations()
            ->withBasicRelationCounts()
            ->find($request->input('id'));

        // Append basic attributes with 'general_statuses_with_periods' and return record
        $record->appendBasicAttributes();
        $record->addGeneralStatusPeriods();

        return $record;
    }

    /**
     * AJAX request
     */
    public function updateRegisteredInAspValue(Request $request): Process
    {
        $record = Process::withTrashed()
            ->with('status.generalStatus') // Required for 'is_ready_for_asp_registration' attribute
            ->findOrFail($request->input('id'));

        // Return error if record isn`t ready for ASP registration
        if (!$record->is_ready_for_asp_registration) {
            abort(403);
        }

        // Update record
        $record->update([
            'registered_in_asp' => $request->boolean('new_value'),
        ]);

        // Refetch record because relations lost after update() call
        $record = Process::withTrashed()
            ->withBasicRelations()
            ->withBasicRelationCounts()
            ->find($request->input('id'));

        // Append basic attributes with 'general_statuses_with_periods' and return record
        $record->appendBasicAttributes();
        $record->addGeneralStatusPeriods();

        return $record;
    }

    /**
     * AJAX request
     */
    public function updateReadyForOrderValue(Request $request): Process
    {
        $record = Process::withTrashed()
            ->with('status.generalStatus') // Required for 'can_be_marked_as_ready_for_order' attribute
            ->findOrFail($request->input('id'));

        // Return error if record isn`t ready for ASP contract
        if (!$record->can_be_marked_as_ready_for_order) {
            abort(403);
        }

        // Get new value
        $isReady = $request->boolean('new_value', false);

        // Mark as ready for order
        if ($isReady && !$record->readiness_for_order_date) {
            $record->update([
                'readiness_for_order_date' => now(),
            ]);

            // Send notification (basically to PLD)
            User::notifyUsersBasedOnPermission(
                new ProcessMarkedAsReadyForOrder($record),
                'receive-notification-when-MAD-VPS-is-marked-as-ready-for-order'
            );
        }

        // Unmark as ready for order
        else {
            // Return error if process already has orders
            if ($record->orderProducts()->exists()) {
                throw ValidationException::withMessages([
                    'has_orders' => trans('validation.custom.process.marked_as_ready_for_order_has_orders'),
                ]);
            };

            // Unmark as ready for order
            $record->update([
                'readiness_for_order_date' => null,
            ]);
        }

        // Refetch record because relations lost after update() call
        $record = Process::withTrashed()
            ->withBasicRelations()
            ->withBasicRelationCounts()
            ->find($request->input('id'));

        // Append basic attributes with 'general_statuses_with_periods' and return record
        $record->appendBasicAttributes();
        $record->addGeneralStatusPeriods();

        return $record;
    }

    private function getSimpleFilterDependencies(): array
    {
        return [
            'deadlineStatusOptions' => Process::getDeadlineStatusOptions(),
            'countriesOrderedByName' => Country::orderByName()->get(),
            'analystUsers' => User::getMADAnalystsMinified(),
            'bdmUsers' => User::getCMDBDMsMinifed(),
            'responsiblePeople' => ProcessResponsiblePerson::orderByName()->get(),
            'MAHs' => MarketingAuthorizationHolder::orderByName()->get(),
            'productClasses' => ProductClass::orderByName()->get(),
            'manufacturerCategories' => ManufacturerCategory::orderByName()->get(),
            'generalStatuses' => ProcessGeneralStatus::all(),
            'generalStatusNamesForAnalysts' => ProcessGeneralStatus::getUniqueNamesForAnalysts(),
            'regions' => Country::getRegionOptions(),
            'brands' => Product::getAllUniqueBrands(),
        ];
    }
}
