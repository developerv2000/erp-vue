<?php

namespace App\Http\Controllers\MAD\Services;

use App\Models\Country;
use App\Models\Manufacturer;
use App\Models\Process;
use App\Models\ProcessGeneralStatus;
use App\Support\Helpers\GeneralHelper;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MADKPIService
{
    protected object $request;
    protected int $year;
    protected Collection $months;
    protected Collection $countries;
    protected Collection $generalStatuses;
    protected bool $entendedVersion;

    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * There are two types of KPI versions: MINIFIED and EXTENDED.
     * On MINIFIED version only first 5 stages of general statuses are displayed.
     * On EXTENDED version all stages of general statuses are displayed.
     *
     * IMPORTANT: Special queries and links used for stages 5(Кк) and 7(НПР).
     * No differences for for stages 5(Кк) and 7(НПР) between MINIFIED and EXTENDED versions.
     */
    public function getKPI()
    {
        $this->resolveDependencies();

        // General statuses
        $this->calculateAllGeneralStatusProcessCounts();
        $this->addAllGeneralStatusProcessCountLinks();

        // Months
        $this->addSumOfAllProcessesCountsForMonths();
        $this->addActiveManufacturersCountsForMonths();
        $this->addActiveManufacturersLinksForMonths();

        // Countries
        $this->addProcessCountsForGeneralStatusesOfCountries();
        $this->addYearProcessesCountsForCountries();
        $this->addKPILinksForCountries();

        return [
            'entendedVersion' => $this->entendedVersion,
            'year' => $this->year,
            'months' => $this->months,
            'countries' => $this->countries,
            'generalStatuses' => $this->generalStatuses,
            'currentProcessesCountOfYear' => $this->generalStatuses->sum('year_current_processes_count'),
            'maximumProcessesCountOfYear' => $this->generalStatuses->sum('year_maximum_processes_count'),
            'activeManufacturersOfYear' => $this->months->sum('active_manufacturers_count'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Resolves
    |--------------------------------------------------------------------------
    */

    protected function resolveDependencies()
    {
        $this->resolveRequest();
        $this->resolveYear();
        $this->resolveMonths();
        $this->resolveCountries();
        $this->resolveExtendedVersion();
        $this->resolveGeneralStatuses();
    }

    protected function resolveRequest()
    {
        $user = auth()->user();

        // Restrict by permissions
        if (Gate::denies('view-MAD-KPI-of-all-analysts')) {
            $this->request->merge(['manufacturer_analyst_user_id' => $user->id]);
        }
    }

    protected function resolveYear()
    {
        $this->year = $this->request->input('year', date('Y'));
    }

    protected function resolveMonths()
    {
        $this->months = GeneralHelper::collectCalendarMonths();

        if ($this->request->filled('months')) {
            $this->months = $this->months->whereIn('id', $this->request->input('months'));
        }
    }

    protected function resolveCountries()
    {
        $this->countries = Country::orderByProcessesCount()
            // Get only requested countries when 'country_id' is filled
            ->when($this->request->filled('country_id'), function ($query) {
                return $query->whereIn('id', $this->request->input('country_id'));
            })
            // Else get only countries with processes when 'country_id' is not filled
            ->when(!$this->request->filled('country_id'), function ($query) {
                return $query->whereHas('processes');
            })
            ->get();
    }

    protected function resolveExtendedVersion()
    {
        // Restrict by permission
        $this->entendedVersion = false;

        if (Gate::allows('view-MAD-extended-KPI-version')) {
            $this->entendedVersion = $this->request->input('extended_version', false);
        }
    }

    protected function resolveGeneralStatuses()
    {
        $this->generalStatuses = ProcessGeneralStatus::when(!$this->entendedVersion, function ($query) {
            $query->where('stage', '<=', 5);
        })
            ->orderBy('stage', 'asc')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Process counts of general statuses
    |--------------------------------------------------------------------------
    */

    protected function calculateAllGeneralStatusProcessCounts()
    {
        $this->addCurrentProcessCountsForGeneralStatusMonths();
        $this->addMaximumProcessCountsForGeneralStatusMonths();
        $this->addYearProcessesCountForGeneralStatuses();
    }

    /**
     * Add 'current_processes_count' for each months of general statuses.
     *
     * Iterates through general statuses and months to add 'current_processes_count' for each month of statuses.
     * IMPORTANT: Special queries used for stages 5(Кк) and 7(НПР).
     */
    protected function addCurrentProcessCountsForGeneralStatusMonths()
    {
        foreach ($this->generalStatuses as $status) {
            foreach ($this->months as $month) {
                // Prepare query
                $query = Process::query();

                // Clone request and preapare it for model querying,
                // because Process model filters by request params.
                $request = $this->request->duplicate();

                // Special request params added for 5(Kk) and 7(НПР) stages
                if ($status->stage == 5) {
                    $request->merge($this->getContractedOnSpecificMonthParams($month));
                } elseif ($status->stage == 7) {
                    $request->merge($this->getRegisteredOnSpecificMonthParams($month));
                }

                // Add required query modifications for other stages
                if ($status->stage != 5 && $status->stage != 7) {
                    $query->whereHas('activeStatusHistory', function ($historyQuery) use ($status, $month) {
                        $historyQuery->whereYear('start_date', $this->year)
                            ->whereMonth('start_date', $month['id'])
                            ->whereHas('status.generalStatus', fn($statusesQuery) => $statusesQuery->where('id', $status->id));
                    });
                }

                // Apply base filters and get current processes count
                $count = Process::filterQueryForRequest($query, $request, applyPermissionsFilter: false)
                    ->count();

                // Set the current processes count of the month for the status
                $statusMonths = $status->months;
                $statusMonths[$month['id']]['current_processes_count'] = $count;
                $status->months = $statusMonths;
            }
        }
    }

    /**
     * Add 'maximum_processes_count' for each months of general statuses.
     *
     * Iterates through general statuses and months to add 'maximum_processes_count' for each month of statuses.
     * Special queries are used for 5(Кк) and 7(НПР) stages.
     */
    protected function addMaximumProcessCountsForGeneralStatusMonths()
    {
        foreach ($this->generalStatuses as $status) {
            foreach ($this->months as $month) {
                // Prepare query
                $query = Process::query();

                // Clone request and preapare it for model querying,
                // because Process model filters by request params.
                $request = $this->request->duplicate();

                // Special request params for 5(Kk) and 7(НПР) stages
                // and default params added for other stages
                if ($status->stage == 5) {
                    $request->merge($this->getContractedOnSpecificMonthParams($month));
                } elseif ($status->stage == 7) {
                    $request->merge($this->getRegisteredOnSpecificMonthParams($month));
                } else {
                    $request->merge($this->getHasGeneralStatusHistoryForSpecificMonthParams($month, $status));
                }

                // Apply base filters and get maximum processes count
                $count = Process::filterQueryForRequest($query, $request, applyPermissionsFilter: false)
                    ->count();

                // Set the maximum processes count of the month for the status
                $statusMonths = $status->months;
                $statusMonths[$month['id']]['maximum_processes_count'] = $count;
                $status->months = $statusMonths;
            }
        }
    }

    /**
     * Add 'year_current_processes_count' and 'year_maximum_processes_count' for each general status.
     */
    protected function addYearProcessesCountForGeneralStatuses()
    {
        foreach ($this->generalStatuses as $status) {
            $sumOfCurrentProcesses = 0;
            $sumOfMaximumProcesses = 0;

            foreach ($status->months as $month) {
                $sumOfCurrentProcesses += $month['current_processes_count'];
                $sumOfMaximumProcesses += $month['maximum_processes_count'];
            }

            $status['year_current_processes_count'] = $sumOfCurrentProcesses;
            $status['year_maximum_processes_count'] = $sumOfMaximumProcesses;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Process counts links of general statuses
    |--------------------------------------------------------------------------
    */

    protected function addAllGeneralStatusProcessCountLinks()
    {
        $this->addCurrentProcessCountLinksForGeneralStatusMonths();
        $this->addMaximumProcessCountLinksForGeneralStatusMonths();
    }

    /**
     * Add 'current_processes_link' for each month of general statuses.
     */
    protected function addCurrentProcessCountLinksForGeneralStatusMonths()
    {
        // Get required filter query parameters from request
        $filterQueryParams = $this->getFilterQueryParamsForProcessesIndexPage();

        foreach ($this->generalStatuses as $status) {
            foreach ($this->months as $month) {
                $queryParams = $filterQueryParams;

                // Special query for stage 5(Kk)
                if ($status->stage == 5) {
                    $queryParams = [
                        ...$queryParams,
                        ...$this->getContractedOnSpecificMonthParams($month),
                    ];

                    // Special query for stage 7(НПР)
                } else if ($status->stage == 7) {
                    $queryParams = [
                        ...$queryParams,
                        ...$this->getRegisteredOnSpecificMonthParams($month),
                    ];

                    // Default query
                } else {
                    $queryParams['initialize_from_inertia_page'] = true; // Reset pinia store filters
                    $queryParams['status_general_status_id[]'] = $status->id;
                    $queryParams['active_status_start_date_range'] = $this->generateMonthRangeForSpecificMonth($month);
                }

                $link = route('mad.processes.index', $queryParams);

                // Set the current processes link of the month for the status
                $statusMonths = $status->months;
                $statusMonths[$month['id']]['current_processes_link'] = $link;
                $status->months = $statusMonths;
            }
        }
    }

    /**
     * Add 'maximum_processes_link' for each month of general statuses.
     */
    protected function addMaximumProcessCountLinksForGeneralStatusMonths()
    {
        // Get required filter query parameters from request
        $filterQueryParams = $this->getFilterQueryParamsForProcessesIndexPage();

        foreach ($this->generalStatuses as $status) {
            foreach ($this->months as $month) {
                // Special query for stage 5(Kk)
                if ($status->stage == 5) {
                    $queryParams = [
                        ...$filterQueryParams,
                        ...$this->getContractedOnSpecificMonthParams($month),
                    ];

                    // Special query for stage 7(НПР)
                } else if ($status->stage == 7) {
                    $queryParams = [
                        ...$filterQueryParams,
                        ...$this->getRegisteredOnSpecificMonthParams($month),
                    ];

                    // Default query
                } else {
                    $queryParams = [
                        ...$filterQueryParams,
                        ...$this->getHasGeneralStatusHistoryForSpecificMonthParams($month, $status),
                    ];
                }

                $link = route('mad.processes.index', $queryParams);

                // Set the current processes link of the month for the status
                $statusMonths = $status->months;
                $statusMonths[$month['id']]['maximum_processes_link'] = $link;
                $status->months = $statusMonths;
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Month calculations and links
    |--------------------------------------------------------------------------
    */

    /**
     * Add 'sum_of_all_current_process_count' and 'sum_of_all_maximum_process_count' for each month.
     */
    protected function addSumOfAllProcessesCountsForMonths()
    {
        foreach ($this->months as $month) {
            $sumOfCurrentProcesses = 0;
            $sumOfMaximumProcesses = 0;

            foreach ($this->generalStatuses as $status) {
                $sumOfCurrentProcesses += $status->months[$month['id']]['current_processes_count'];
                $sumOfMaximumProcesses += $status->months[$month['id']]['maximum_processes_count'];
            }

            $month['sum_of_all_current_process_count'] = $sumOfCurrentProcesses;
            $month['sum_of_all_maximum_process_count'] = $sumOfMaximumProcesses;
        }
    }

    /**
     * Add 'active_manufacturers_count' for each month.
     */
    protected function addActiveManufacturersCountsForMonths()
    {
        foreach ($this->months as $month) {
            $query = Manufacturer::query();
            $request = $this->request->duplicate();

            // Validate 'country_id' of request, which represents
            // 'country_id' of Process, not Manufacturer
            if ($request->has('country_id')) {
                $request->merge([
                    'process_country_id' => $request->country_id,
                    'country_id' => null,
                ]);
            }

            // Merge required params to request
            $request->merge($this->getHasActiveProcessesForSpecificMonthParams($month));

            // Apply base filters
            $query = Manufacturer::filterQueryForRequest($query, $request);

            // Set active manufacturers count for month
            $month['active_manufacturers_count'] = $query->count();;
        }
    }

    /**
     * Add 'active_manufacturers_link' for each month
     */
    protected function addActiveManufacturersLinksForMonths()
    {
        // Get vaidated filter query parameters from request
        $filterQueryParams = [
            'analyst_user_id' => $this->request->manufacturer_analyst_user_id,
            'bdm_user_id' => $this->request->manufacturer_bdm_user_id,
            'region' => $this->request->manufacturer_region,
        ];

        foreach ($this->months as $month) {
            $queryParams = [
                ...$filterQueryParams,
                ...$this->getHasActiveProcessesForSpecificMonthParams($month),
            ];

            // Generate and assign link for month
            $link = route('mad.manufacturers.index', $queryParams);
            $month['active_manufacturers_link'] = $link;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Countries calculations and links
    |--------------------------------------------------------------------------
    */

    protected function addProcessCountsForGeneralStatusesOfCountries()
    {
        // Pluck month ids for filtering
        $monthIDs = $this->months->pluck('id')->toArray();

        foreach ($this->countries as $country) {
            $country->statuses = [];

            // Calculate processes count for each general statuses
            foreach ($this->generalStatuses as $status) {
                $query = Process::query();
                $request = $this->request->duplicate();

                // Filter 'country_id'
                $query->where('country_id', $country->id);

                // Special query for stage 5(Kk)
                if ($status->stage == 5) {
                    $request->merge($this->getContractedOnSpecificMonthsParams($monthIDs));
                    // Special query for stage 7(НПР)
                } elseif ($status->stage == 7) {
                    $request->merge($this->getRegisteredOnSpecificMonthsParams($monthIDs));
                    // Default query
                } else {
                    $query->whereHas('activeStatusHistory', function ($historyQuery) use ($status, $monthIDs) {
                        $historyQuery->whereYear('start_date', $this->year)
                            ->whereIn(DB::raw('MONTH(start_date)'), $monthIDs)
                            ->whereHas('status.generalStatus', fn($statusesQuery) => $statusesQuery->where('id', $status->id));
                    });
                }

                // Apply base filters
                Process::filterQueryForRequest($query, $request, applyPermissionsFilter: false);

                // Set processes count for general status of country
                $statuses = $country->statuses;
                $statuses[$status->name]['processes_count'] = $query->count();
                $country->statuses = $statuses;
            }
        }
    }

    /**
     * Add 'year_processes_count' for each country
     */
    protected function addYearProcessesCountsForCountries()
    {
        foreach ($this->countries as $country) {
            $sum = 0;

            foreach ($country->statuses as $status) {
                $sum += $status['processes_count'];
            }

            $country->year_processes_count = $sum;
        }
    }

    /**
     * Add 'kpi_link' for each country
     *
     * Used on 'map' type chart, when clicking specific country!
     */
    protected function addKPILinksForCountries()
    {
        // Get required filter query parameters from request
        $filterQueryParams = $this->request->except(['country_id']);

        foreach ($this->countries as $country) {
            $queryParams = $filterQueryParams;
            $queryParams['country_id'] = [$country->id];
            $queryParams['initialize_from_inertia_page'] = true; // Reset pinia store filters

            $link = route('mad.kpi.index', $queryParams);
            $country['kpi_link'] = $link;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Used in below cases:
     *
     * 1. When calculating 'current_processes_count' and 'maximum_processes_count'
     *  for months of 5(Кк) stage, by merging parameters into request.
     *
     * 2. When generating 'current_processes_link' and 'maximum_processes_link'
     *  for months of 5(Кк) stage, by merging parameters into links query.
     */
    protected function getContractedOnSpecificMonthParams($month): array
    {
        return [
            'contracted_on_specific_month' => true,
            'contracted_on_year' => $this->year,
            'contracted_on_month' => $month['id'],
            'initialize_from_inertia_page' => true, // Reset pinia store filters
        ];
    }

    /**
     * Used in below cases:
     *
     * 1. When calculating 'current_processes_count' and 'maximum_processes_count'
     *  for months of 7(НПР) stage, by merging parameters into request.
     *
     * 2. When generating 'current_processes_link' and 'maximum_processes_link'
     *  for months of 7(НПР) stage, by merging parameters into links query.
     */
    protected function getRegisteredOnSpecificMonthParams($month): array
    {
        return [
            'registered_on_specific_month' => true,
            'registered_on_year' => $this->year,
            'registered_on_month' => $month['id'],
            'initialize_from_inertia_page' => true, // Reset pinia store filters
        ];
    }

    /**
     * Used in below cases:
     *
     * 1. When calculating 'maximum_processes_count' for months of general statuses
     * not (5(Кк) and 7(НПР)), by merging parameters into request.
     *
     * 2. When generating 'maximum_processes_link' for months of general statuses
     * not (5(Кк) and 7(НПР)), by merging parameters into request.
     */
    protected function getHasGeneralStatusHistoryForSpecificMonthParams($month, $status): array
    {
        return [
            'has_general_status_history' => true,
            'has_general_status_for_year' => $this->year,
            'has_general_status_for_month' => $month['id'],
            'has_general_status_id' => $status->id,
            'initialize_from_inertia_page' => true, // Reset pinia store filters
        ];
    }

    /**
     * Get only required filter query parameters from the request for Process index page.
     *
     * Used when generating 'current_processes_link' and 'maximum_processes_link'
     * for months of all general statuses.
     */
    protected function getFilterQueryParamsForProcessesIndexPage(): array
    {
        return $this->request->only([
            'manufacturer_analyst_user_id',
            'manufacturer_bdm_user_id',
            'country_id',
            'region',
        ]);
    }

    /**
     * Used in below cases:
     *
     * 1. When calculating 'active_manufacturers_count' of months,
     * by merging parameters into request.
     *
     * 2. When generating 'active_manufacturers_link' of months,
     * by merging parameters into links query.
     */
    protected function getHasActiveProcessesForSpecificMonthParams($month): array
    {
        return [
            'has_active_processes_for_specific_month' => true,
            'has_active_processes_for_year' => $this->year,
            'has_active_processes_for_month' => $month['id'],
            'initialize_from_inertia_page' => true, // Reset pinia store filters
        ];
    }

    /**
     * Generate month range in format:
     * given_month_d/m/y - last_day_of_month_d/m/y
     *
     * Used only when generating 'current_processes_link' for months of general statuses
     * not (5(Кк) and 7(НПР)), by merging parameters into links query.
     */
    protected function generateMonthRangeForSpecificMonth($month): string
    {
        $monthStart = Carbon::createFromFormat(
            'Y-m-d',
            $this->year . '-' . $month['id'] . '-01'
        );

        $monthEnd = $monthStart->copy()->endOfMonth();

        return $monthStart->format('Y-m-d') . ' - ' . $monthEnd->format('Y-m-d');
    }

    /**
     * Used only when calculating 'processes_count' of general statuses of countries,
     * when general status is 5(Кк), by merging parameters into request.
     */
    protected function getContractedOnSpecificMonthsParams(array $monthIDs): array
    {
        return [
            'contracted_on_specific_months' => true,
            'contracted_on_year' => $this->year,
            'contracted_on_months' => $monthIDs,
        ];
    }

    /**
     * Used only when calculating 'processes_count' of general statuses of countries,
     * when general status is 7(НПР), by merging parameters into request.
     */
    protected function getRegisteredOnSpecificMonthsParams(array $monthIDs): array
    {
        return [
            'registered_on_specific_months' => true,
            'registered_on_year' => $this->year,
            'registered_on_months' => $monthIDs,
        ];
    }
}
