<?php

namespace App\Http\Controllers\MAD;

use App\Http\Controllers\Controller;
use App\Http\Requests\MadAspStoreRequest;
use App\Http\Requests\MadAspUpdateRequest;
use App\Models\Country;
use App\Models\MadAsp;
use App\Models\MarketingAuthorizationHolder;
use App\Support\Helpers\GeneralHelper;
use App\Support\Helpers\UrlHelper;
use App\Support\Traits\Controller\DestroysModelRecords;
use Illuminate\Http\Request;

class MADASPController extends Controller
{
    use DestroysModelRecords;

    // used in multiple destroy trait
    public static $model = MadAsp::class;

    public function index(Request $request)
    {
        // Preapare request for valid model querying
        MadAsp::addDefaultQueryParamsToRequest($request);
        UrlHelper::addUrlWithReversedOrderTypeToRequest($request);

        // Get finalized records paginated
        $query = MadAsp::withBasicRelations()->withBasicRelationCounts();
        $records = MadAsp::finalizeQueryForRequest($query, $request, 'paginate');

        // Check if ASP exists for current year
        $currentYearASP = $records->where('year', date('Y'))->first();

        // Make all calculations for JS graph
        if ($currentYearASP) {
            $currentYearASP->makeAllCalculations($request);
        }

        // Compact required variables for JS graph as $asp,
        // because single JS function is used to display graphs for both index & show pages
        $asp = [
            'countries' => $currentYearASP ? array_values($currentYearASP->countries->sortByDesc('year_contract_plan')->toArray()) : null, // Get sorted
            'year_contract_fact_percentage' => $currentYearASP?->year_contract_fact_percentage,
        ];

        return view('MAD.asp.index', compact('request', 'records', 'currentYearASP', 'asp'));
    }

    public function show(Request $request, MadAsp $record)
    {
        // Get display options
        $displayOptions = $request->input('display_options', MadAsp::getFilterDisplayOptions());
        $displayQuarters = in_array('Quarters', $displayOptions);
        $displayMonths = in_array('Months', $displayOptions);

        // Make all calculations
        $record->makeAllCalculations($request);

        // Collect months
        $months = GeneralHelper::collectCalendarMonths();

        // Compact required variables for JS graph as $asp,
        // because single JS function is used to display graphs for both index & show pages
        $asp = [
            'countries' => array_values($record->countries->sortByDesc('year_contract_plan')->toArray()), // Get sorted
        ];

        return view('MAD.asp.show', compact('record', 'months', 'displayQuarters', 'displayMonths', 'asp'));
    }

    public function create()
    {
        return view('MAD.asp.create');
    }

    public function store(MadAspStoreRequest $request)
    {
        MadAsp::createFromRequest($request);

        return to_route('mad.asp.index');
    }

    public function edit(Request $request, MadAsp $record)
    {
        return view('MAD.asp.edit', compact('record'));
    }

    public function update(MadAspUpdateRequest $request, MadAsp $record)
    {
        $record->updateFromRequest($request);

        return redirect($request->input('previous_url'));
    }

    public function exportAsExcel(Request $request, MadAsp $record)
    {
        return $record->exportAsExcel($request);
    }

    /*
    |--------------------------------------------------------------------------
    | Countries
    |--------------------------------------------------------------------------
    */

    public function countriesIndex(MadAsp $record)
    {
        $record->load(['countries', 'MAHs']);
        $record->attachAllCountryMAHs();

        return view('MAD.asp.countries.index', compact('record'));
    }

    public function countriesCreate(MadAsp $record)
    {
        return view('MAD.asp.countries.create', compact('record'));
    }

    public function countriesStore(Request $request, MadAsp $record)
    {
        $record->attachCountryOnCountryCreate($request);

        return to_route('mad.asp.countries.index', $record->year);
    }

    public function countriesDestroy(Request $request, MadAsp $record)
    {
        $record->detachCountriesByID($request->input('ids', []));

        return redirect()->back();
    }

    /*
    |--------------------------------------------------------------------------
    | MAH
    |--------------------------------------------------------------------------
    */

    public function MAHsIndex(MadAsp $record, Country $country)
    {
        $MAHs = $record->MAHsOfSpecificCountry($country)->get();

        return view('MAD.asp.mahs.index', compact('record', 'country', 'MAHs'));
    }

    public function MAHsCreate(MadAsp $record, Country $country)
    {
        return view('MAD.asp.mahs.create', compact('record', 'country'));
    }

    public function MAHsStore(Request $request, MadAsp $record, Country $country)
    {
        $record->attachMAHOnMAHCreate($request);

        return to_route('mad.asp.mahs.index', ['record' => $record->year, 'country' => $country->id]);
    }

    public function MAHsEdit(MadAsp $record, Country $country, MarketingAuthorizationHolder $mah)
    {
        $mah = $record->MAHsOfSpecificCountry($country)
            ->where('marketing_authorization_holders.id', $mah->id)->first();

        return view('MAD.asp.mahs.edit', compact('record', 'country', 'mah'));
    }

    public function MAHsUpdate(Request $request, MadAsp $record, Country $country, MarketingAuthorizationHolder $mah)
    {
        $record->updateMAHFromRequest($mah, $country, $request);

        return to_route('mad.asp.mahs.index', ['record' => $record->year, 'country' => $country->id]);
    }

    public function MAHsDestroy(Request $request, MadAsp $record, Country $country)
    {
        $record->detachCountryMAHsByID($country, $request->input('ids', []));

        return redirect()->back();
    }
}
