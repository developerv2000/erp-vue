<?php

namespace App\Http\Controllers\CMD;

use App\Http\Controllers\Controller;
use App\Http\Requests\CMD\CMDInvoiceStoreProductionTypeRequest;
use App\Http\Requests\CMD\CMDInvoiceUpdateProductionTypeRequest;
use App\Models\Country;
use App\Models\Invoice;
use App\Models\InvoicePaymentType;
use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CMDInvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $getAllTableHeaders = fn() => $request->user()->collectTranslatedTableHeadersByKey(User::CMD_INVOICES_HEADERS_KEY);
        $getVisibleHeaders = fn() => User::filterOnlyVisibleTableHeaders($getAllTableHeaders());

        return Inertia::render('departments/CMD/pages/invoices/Index', [
            // Lazy loads. Refetched only on headers update and locale change
            'allTableHeaders' => $getAllTableHeaders,
            'tableVisibleHeaders' => $getVisibleHeaders,

            // Lazy loads. Never refetched again
            'filterDependencies' => fn() => $this->getFilterDependencies(),
        ]);
    }

    public function create(Request $request): Response
    {
        // Secure action
        $order = Order::findOrFail($request->input('order_id'));

        $this->authorizeOrderEdit($order);

        if (!$order->can_attach_any_production_invoice) {
            abort(404);
        }

        // Get available products for invoice based on payment type
        $paymentType = InvoicePaymentType::findOrFail($request->input('payment_type_id'));
        $availableProducts = $this->getAvailableProductsForInvoiceOnCreate($order, $paymentType);

        // No lazy loads required, because user is redirected back on store
        return Inertia::render('departments/CMD/pages/invoices/Create', [
            'order' => $order,
            'paymentType' => $paymentType,
            'isPrepayment' => $paymentType->name == InvoicePaymentType::PREPAYMENT_NAME,
            'availableProducts' => $availableProducts,
        ]);
    }

    /**
     * AJAX request
     */
    public function store(CMDInvoiceStoreProductionTypeRequest $request): JsonResponse
    {
        // Secure action
        $order = Order::findorfail($request->input('order_id'));
        $this->authorizeOrderEdit($order);

        // Create invoice
        Invoice::storeProductionTypeByCMDFromRequest($request, $order);

        // Return response
        return response()->json([
            'success' => true,
        ]);
    }

    public function edit($record): Response
    {
        // Fetch record with relations
        $record = Invoice::withBasicCMDRelations()
            ->withBasicCMDRelationCounts()
            ->findorfail($record);

        // Secure action
        $this->authorizeInvoiceEdit($record);

        // Append additional attributes
        $record->appendBasicCMDAttributes();
        $record->append('title'); // Used on generating breadcrumbs

        // Get available products for invoice based on payment type
        $availableProducts = $this->getAvailableProductsForInvoiceOnEdit($record);

        // Return response
        return Inertia::render('departments/CMD/pages/invoices/Edit', [
            // Refetched after record update
            'record' => $record,
            'availableProducts' => $availableProducts,

            // Never refetched again
            'isPrepayment' => $record->paymentType->name == InvoicePaymentType::PREPAYMENT_NAME,
        ]);
    }

    /**
     * AJAX request
     */
    public function update(CMDInvoiceUpdateProductionTypeRequest $request, Invoice $record): JsonResponse
    {
        // Secure action
        $this->authorizeInvoiceEdit($record);

        // Update invoice
        $record->updateProductionTypeByCMDFromRequest($request);

        // Return response
        return response()->json([
            'success' => true,
        ]);
    }

    private function getAvailableProductsForInvoiceOnCreate(Order $order, InvoicePaymentType $paymentType): Collection
    {
        $products = match ($paymentType->name) {
            // Display all products as 'readonly' for invoice of PREPAYMENT type
            InvoicePaymentType::PREPAYMENT_NAME
            => $order->products,

            // Display selectable products list for invoice of FINAL_PAYMENT type
            InvoicePaymentType::FINAL_PAYMENT_NAME
            => $order->products->filter(
                fn($product) => $product->can_attach_production_final_payment_invoice
            ),

            // Display selectable products list for invoice of FULL_PAYMENT type
            InvoicePaymentType::FULL_PAYMENT_NAME
            => $order->products->filter(
                fn($product) => $product->can_attach_production_full_payment_invoice
            ),
        };

        // Append additional attributes
        $products->each(fn($product) => $product->appendBasicCMDAttributes());

        // Force array-like structure for proper rendering
        return $products->values();
    }

    private function getAvailableProductsForInvoiceOnEdit(Invoice $invoice): Collection
    {
        $products = match ($invoice->paymentType->name) {
            // Display all products as 'readonly' for invoice of PREPAYMENT type
            InvoicePaymentType::PREPAYMENT_NAME
            => $invoice->products,

            // Display selectable products list for invoice of FINAL_PAYMENT type
            // Concat attached invoice products with order products which can also be attached.
            InvoicePaymentType::FINAL_PAYMENT_NAME
            => $invoice->products->concat($invoice->invoiceable->products->filter(fn(OrderProduct $product) => $product->can_attach_production_final_payment_invoice)),

            // Display selectable products list for invoice of FINAL_PAYMENT type
            // Concat attached invoice products with order products which can also be attached.
            InvoicePaymentType::FULL_PAYMENT_NAME
            => $invoice->products->concat($invoice->invoiceable->products->filter(fn(OrderProduct $product) => $product->can_attach_production_full_payment_invoice)),
        };

        // Append additional attributes
        $products->each(fn($product) => $product->appendBasicCMDAttributes());

        // Force array-like structure for proper rendering
        return $products->values();
    }

    /**
     * AJAX request
     */
    public function sendForPayment(Invoice $record): Invoice
    {
        // Secure action
        $this->authorizeInvoiceEdit($record);

        // Send for payment
        $record->sendProductionTypeForPaymentByCMD();

        // Return refetched updated record
        $record = Invoice::withBasicCMDRelations()
            ->withBasicCMDRelationCounts()
            ->findOrFail($record->id);

        $record->appendBasicCMDAttributes();

        return $record;
    }

    public function destroy(Request $request): JsonResponse
    {
        // Extract id or ids from request as array to delete through loop
        $ids = (array) ($request->input('id') ?: $request->input('ids'));

        DB::transaction(function () use ($ids) {
            foreach ($ids as $id) {
                // Check if model exists before soft deleting
                $record = Invoice::find($id);
                if ($record) {
                    // Secure action
                    $this->authorizeInvoiceEdit($record);

                    // Delete record
                    $record->delete();
                }
            }
        });

        return response()->json([
            'count' => count($ids),
        ]);
    }

    private function getFilterDependencies(): array
    {
        return [
            'paymentTypes' => InvoicePaymentType::orderBy('id')->get(),
            'invoiceNumbers' => Invoice::onlyProductionType()->whereNotNull('number')->orderBy('number')->get()->pluck('number'),
            'orderNames' => Order::onlyWithName()->orderByName()->pluck('name'),
            'manufacturers' => Manufacturer::getMinifiedRecordsWithProcessesReadyForOrder(),
            'countriesOrderedByProcessesCount' => Country::orderByProcessesCount()->get(),
        ];
    }

    private function authorizeOrderEdit(Order $order): void
    {
        Gate::authorize('edit-CMD-order', $order);
    }

    private function authorizeInvoiceEdit(Invoice $invoice): void
    {
        Gate::authorize('edit-CMD-invoice', $invoice);
    }
}
