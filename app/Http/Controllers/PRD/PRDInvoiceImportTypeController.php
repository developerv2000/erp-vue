<?php

namespace App\Http\Controllers\PRD;

use App\Http\Controllers\Controller;
use App\Http\Requests\PRD\PRDInvoiceUpdateImportTypeRequest;
use App\Models\Invoice;
use App\Models\Manufacturer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PRDInvoiceImportTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $getAllTableHeaders =
            fn() => $request->user()->collectTranslatedTableHeadersByKey(User::PRD_IMPORT_TYPE_INVOICES_HEADERS_KEY);

        $getVisibleHeaders = fn() => User::filterOnlyVisibleTableHeaders($getAllTableHeaders());

        return Inertia::render('departments/PRD/pages/invoices/import-types/Index', [
            // Lazy loads. Refetched only on headers update and locale change
            'allTableHeaders' => $getAllTableHeaders,
            'tableVisibleHeaders' => $getVisibleHeaders,

            // Lazy loads. Never refetched again
            'filterDependencies' => fn() => $this->getFilterDependencies(),
        ]);
    }

    public function edit($record): Response
    {
        $record = Invoice::withBasicPRDImportTypesRelations()
            ->withBasicPRDImportTypesRelationCounts()
            ->findorfail($record);

        $record->appendBasicPRDImportTypesAttributes();
        $record->append('title'); // Used on generating breadcrumbs

        return Inertia::render('departments/PRD/pages/invoices/import-types/Edit', [
            // Refetched after record update
            'record' => $record,
        ]);
    }

    /**
     * AJAX request
     */
    public function update(PRDInvoiceUpdateImportTypeRequest $request, Invoice $record): JsonResponse
    {
        $record->updateImportTypeByPRDFromRequest($request);

        return response()->json([
            'success' => true,
        ]);
    }

    private function getFilterDependencies(): array
    {
        return [
            'invoiceNumbers' => Invoice::onlyImportType()->whereNotNull('number')->orderBy('number')->get()->pluck('number'),
            'manufacturers' => Manufacturer::getMinifiedRecordsWithProcessesReadyForOrder(),
        ];
    }
}
