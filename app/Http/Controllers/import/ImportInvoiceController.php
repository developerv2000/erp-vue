<?php

namespace App\Http\Controllers\import;

use App\Http\Controllers\Controller;
use App\Http\Requests\import\ImportInvoiceStoreRequest;
use App\Http\Requests\import\ImportInvoiceUpdateRequest;
use App\Models\Country;
use App\Models\Invoice;
use App\Models\InvoicePaymentType;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\User;
use App\Support\Traits\Controller\DestroysModelRecords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ImportInvoiceController extends Controller
{
    use DestroysModelRecords;

    // Required for DestroysModelRecords trait
    public static $model = Invoice::class;

    public function index(Request $request): Response
    {
        $getAllTableHeaders = fn() => $request->user()->collectTranslatedTableHeadersByKey(User::IMPORT_INVOICES_HEADERS_KEY);
        $getVisibleHeaders = fn() => User::filterOnlyVisibleTableHeaders($getAllTableHeaders());

        return Inertia::render('sections/import/pages/invoices/Index', [
            // Lazy loads. Refetched only on headers update and locale change
            'allTableHeaders' => $getAllTableHeaders,
            'tableVisibleHeaders' => $getVisibleHeaders,

            // Lazy loads. Never refetched again
            'filterDependencies' => fn() => $this->getFilterDependencies(),
        ]);
    }

    public function create(Request $request): Response
    {
        // Check if shipment can attach invoice
        $shipment = Shipment::findOrFail($request->input('shipment_id'));

        if (!$shipment->can_attach_import_invoice) {
            abort(404);
        }

        // Used on breadcrumb generation
        $shipment->append('title');

        // No lazy loads required, because user is redirected back on store
        return Inertia::render('sections/import/pages/invoices/Create', [
            'shipment' => $shipment,
        ]);
    }

    /**
     * AJAX request
     */
    public function store(ImportInvoiceStoreRequest $request): JsonResponse
    {
        Invoice::storeImportTypeByELDFromRequest($request);

        return response()->json([
            'success' => true,
        ]);
    }

    public function edit($record): Response
    {
        $record = Invoice::withBasicImportRelations()
            ->withBasicImportRelationCounts()
            ->findorfail($record);

        $record->appendBasicImportAttributes();
        $record->append('title'); // Used on generating breadcrumbs

        return Inertia::render('sections/import/pages/invoices/Edit', [
            // Refetched after record update
            'record' => $record,
        ]);
    }

    /**
     * AJAX request
     */
    public function update(ImportInvoiceUpdateRequest $request, Invoice $record): JsonResponse
    {
        $record->updateImportTypeByELDFromRequest($request);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * AJAX request
     */
    public function sendForPayment(Invoice $record): Invoice
    {
        $record->sendImportTypeForPaymentByELD();

        // Return refetched updated record
        $record = Invoice::withBasicImportRelations()
            ->withBasicImportRelationCounts()
            ->findOrFail($record->id);

        $record->appendBasicImportAttributes();

        return $record;
    }

    private function getFilterDependencies(): array
    {
        return [
            'invoiceNumbers' => Invoice::onlyImportType()->orderBy('number')->get(),
            'manufacturers' => Manufacturer::getMinifiedRecordsWithProcessesReadyForOrder(),
        ];
    }
}
