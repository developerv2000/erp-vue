<?php

namespace App\Http\Controllers\PRD;

use App\Http\Controllers\Controller;
use App\Http\Requests\PRD\PRDInvoiceUpdateProductionTypeRequest;
use App\Models\Country;
use App\Models\Invoice;
use App\Models\InvoicePaymentType;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PRDInvoiceProductionTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $getAllTableHeaders =
            fn() => $request->user()->collectTranslatedTableHeadersByKey(User::PRD_PRODUCTION_TYPE_INVOICES_HEADERS_KEY);

        $getVisibleHeaders = fn() => User::filterOnlyVisibleTableHeaders($getAllTableHeaders());

        return Inertia::render('departments/PRD/pages/invoices/production-types/Index', [
            // Lazy loads. Refetched only on headers update and locale change
            'allTableHeaders' => $getAllTableHeaders,
            'tableVisibleHeaders' => $getVisibleHeaders,

            // Lazy loads. Never refetched again
            'filterDependencies' => fn() => $this->getFilterDependencies(),
        ]);
    }

    public function edit($record): Response
    {
        $record = Invoice::withBasicPRDProductionTypesRelations()
            ->withBasicPRDProductionTypesRelationCounts()
            ->findorfail($record);

        $record->appendBasicPRDProductionTypesAttributes();
        $record->append('title'); // Used on generating breadcrumbs

        return Inertia::render('departments/PRD/pages/invoices/production-types/Edit', [
            // Refetched after record update
            'record' => $record,
        ]);
    }

    /**
     * AJAX request
     */
    public function update(PRDInvoiceUpdateProductionTypeRequest $request, Invoice $record): JsonResponse
    {
        $record->updateProductionTypeByPRDFromRequest($request);

        return response()->json([
            'success' => true,
        ]);
    }

    private function getFilterDependencies(): array
    {
        return [
            'paymentTypes' => InvoicePaymentType::orderBy('id')->get(),
            'invoiceNumbers' => Invoice::onlyProductionType()->orderBy('number')->get(),
            'orderNames' => Order::onlyWithName()->orderByName()->pluck('name'),
            'manufacturers' => Manufacturer::getMinifiedRecordsWithProcessesReadyForOrder(),
            'countriesOrderedByProcessesCount' => Country::orderByProcessesCount()->get(),
        ];
    }
}
