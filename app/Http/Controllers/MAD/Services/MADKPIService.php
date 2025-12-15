<?php

namespace App\Http\Controllers\MAD\Services;

use App\Models\Country;
use App\Models\Process;
use App\Models\ProcessGeneralStatus;
use App\Support\Helpers\GeneralHelper;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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
        $this->calculateAllGeneralStatusProcessCounts();
        $this->addAllGeneralStatusProcessCountLinks();
        $this->addSumOfAllProcessesCountsForMonths();

        return [
            'entendedVersion' => $this->entendedVersion,
            'year' => $this->year,
            'months' => $this->months,
            'countries' => $this->countries,
            'generalStatuses' => $this->generalStatuses,
            'currentProcessesCountOfYear' => $this->generalStatuses->sum('year_current_processes'),
            'maximumProcessesCountOfYear' => $this->generalStatuses->sum('year_maximum_processes'),
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
        $this->months = GeneralHelper::collectCalendarMonthsTranslated();

        if ($this->request->filled('months')) {
            $this->months = $this->months->whereIn('id', $this->request->input('months'));
        }
    }

    protected function resolveCountries()
    {
        $this->countries = Country::orderByProcessesCount()
            ->when($this->request->filled('country_id'), function ($query) {
                return $query->whereIn('id', $this->request->input('country_id'));
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
    | General status process counts calculations
    |--------------------------------------------------------------------------
    */

    protected function calculateAllGeneralStatusProcessCounts()
    {
        $this->addCurrentProcessCountsForGeneralStatusMonths();
        $this->addMaximumProcessCountsForGeneralStatusMonths();
        $this->addSumOfMonthlyProcessesForGeneralStatuses();
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

                // Update the current processes count of the month for the status
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

                // Update the maximum processes count of the month for the status
                $statusMonths = $status->months;
                $statusMonths[$month['id']]['maximum_processes_count'] = $count;
                $status->months = $statusMonths;
            }
        }
    }

    /**
     * Add 'year_current_processes' and 'year_maximum_processes' for each general status.
     */
    protected function addSumOfMonthlyProcessesForGeneralStatuses()
    {
        foreach ($this->generalStatuses as $status) {
            $sumOfCurrentProcesses = 0;
            $sumOfMaximumProcesses = 0;

            foreach ($status->months as $month) {
                $sumOfCurrentProcesses += $month['current_processes_count'];
                $sumOfMaximumProcesses += $month['maximum_processes_count'];
            }

            $status['year_current_processes'] = $sumOfCurrentProcesses;
            $status['year_maximum_processes'] = $sumOfMaximumProcesses;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | General status process counts links
    |--------------------------------------------------------------------------
    */

    protected function addAllGeneralStatusProcessCountLinks()
    {
        $this->addCurrentProcessCountLinksForGeneralStatusMonths();
        $this->addMaximumProcessCountLinksForGeneralStatusMonths();
    }

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
                    $queryParams['general_status_id[]'] = $status->id;
                    $queryParams['active_status_start_date_range'] = $this->generateMonthRangeForSpecificMonth($month);
                }

                $link = route('mad.processes.index', $queryParams);

                // Update the current processes link of the month for the status
                $statusMonths = $status->months;
                $statusMonths[$month['id']]['current_processes_link'] = $link;
                $status->months = $statusMonths;
            }
        }
    }

    protected function addMaximumProcessCountLinksForGeneralStatusMonths()
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
                    $queryParams = [
                        ...$queryParams,
                        ...$this->getHasGeneralStatusHistoryForSpecificMonthParams($month, $status),
                    ];
                }

                $link = route('mad.processes.index', $queryParams);

                // Update the current processes link of the month for the status
                $statusMonths = $status->months;
                $statusMonths[$month['id']]['maximum_processes_link'] = $link;
                $status->months = $statusMonths;
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Months
    |--------------------------------------------------------------------------
    */

    /**
     * Add 'sum_of_all_current_process' and 'sum_of_all_maximum_process' for each month.
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

            $month['sum_of_all_current_process'] = $sumOfCurrentProcesses;
            $month['sum_of_all_maximum_process'] = $sumOfMaximumProcesses;
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
     * Generate month range in format of given_month_d/m/y - next_month_d/m/y for month.
     *
     * Used only when generating 'current_processes_link' for months of general statuses
     * not (5(Кк) and 7(НПР)), by merging parameters into links query.
     */
    protected function generateMonthRangeForSpecificMonth($month): string
    {
        $monthStart = Carbon::createFromFormat('Y-m-d', $this->year . '-' . $month['id'] . '-01');
        $nextMonthStart = $monthStart->copy()->addMonth()->startOfMonth();

        return $monthStart->format('d/m/Y') . ' - ' . $nextMonthStart->format('d/m/Y');
    }
}
