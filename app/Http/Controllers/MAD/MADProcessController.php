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

            // Lazy loads
            'simpleFilterDependencies' => fn() => ProcessesSimpleFilter::getAllDependencies(),
            'allTableHeaders' => $getAllTableHeaders, // Refetched only on headers update
            'tableVisibleHeaders' => $getVisibleHeaders, // Refetched only on headers update
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

            // Lazy loads, never refetched again
            'simpleFilterDependencies' => fn() => ProcessesSimpleFilter::getAllDependencies(),
            'tableVisibleHeaders' => $getVisibleHeaders,
        ]);
    }

    public function create()
    {
        // No lazy loads required, because AJAX request is used on store
        return Inertia::render('departments/MAD/pages/processes/Create', [
            'countriesOrderedByProcessesCount' => Country::orderByProcessesCount()->get(),
            'responsiblePeople' => ProcessResponsiblePerson::orderByName()->get(),
            'defaultSelectedStatusIDs' => ProcessStatus::getDefaultSelectedIDValue(),
            'countriesOrderedByName' => Country::orderByName()->get(),
            'currencies' => Currency::orderByName()->get(),
            'MAHs' => MarketingAuthorizationHolder::orderByName()->get(),
            'defaultSelectedMAHID' => MarketingAuthorizationHolder::getDefaultSelectedIDValue(),
            'defaultSelectedCurrencyID' => Currency::getDefaultIdValueForMADProcesses(),
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
            'breadcrumbs' => $fetchedRecord->generateBreadcrumbs('mad'),

            // Lazy loads, never refetched again
            'countriesOrderedByProcessesCount' => Country::orderByProcessesCount()->get(),
            'responsiblePeople' => ProcessResponsiblePerson::orderByName()->get(),
            'defaultSelectedStatusIDs' => ProcessStatus::getDefaultSelectedIDValue(),
            'countriesOrderedByName' => Country::orderByName()->get(),
            'currencies' => Currency::orderByName()->get(),
            'MAHs' => MarketingAuthorizationHolder::orderByName()->get(),
            'defaultSelectedMAHID' => MarketingAuthorizationHolder::getDefaultSelectedIDValue(),
            'defaultSelectedCurrencyID' => Currency::getDefaultIdValueForMADProcesses(),
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
            'contracted_in_asp' => $request->input('value'),
        ]);

        // Append basic attributes and return record
        $record->appendBasicAttributes();
        return $record;
    }
}
