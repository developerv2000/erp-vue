<?php

namespace App\Http\Controllers\MAD;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MAD\Services\MADKPIService;
use App\Models\Country;
use App\Models\User;
use App\Support\Helpers\GeneralHelper;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MADKPIController extends Controller
{
    public function index(Request $request): Response
    {
        $service = new MADKPIService($request);

        return Inertia::render('departments/MAD/pages/kpi/Index', [
            // Lazy loads. Refetched only on filter form submit
            'kpiData' => $service->getKPI(),

            // Lazy loads. Never refetched again
            'filterDependencies' => fn() => $this->getFilterDependencies(),
        ]);
    }

    private function getFilterDependencies(): array
    {
        return [
            'months' => GeneralHelper::collectCalendarMonthsTranslated(),
            'analystUsers' => User::getMADAnalystsMinified(),
            'bdmUsers' => User::getCMDBDMsMinifed(),
            'countriesOrderedByProcessesCount' => Country::orderByProcessesCount()->get(),
            'regions' => Country::getRegionOptions(),
        ];
    }
}
