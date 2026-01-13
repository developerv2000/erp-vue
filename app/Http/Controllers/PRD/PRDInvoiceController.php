<?php

namespace App\Http\Controllers\PRD;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceType;

class PRDInvoiceController extends Controller
{
    /**
     * AJAX request
     */
    public function accept(Invoice $record): Invoice
    {
        $record->acceptByPRD();

        // Return refetched updated record
        $record = $this->getRecordByType($record->type, $record->id);

        return $record;
    }

    /**
     * AJAX request
     */
    public function completePayment(Invoice $record): Invoice
    {
        $record->completePaymentByPRD();

        // Return refetched updated record
        $record = $this->getRecordByType($record->type, $record->id);

        return $record;
    }

    private function getRecordByType(InvoiceType $invoiceType, int $id): Invoice
    {
        return match ($invoiceType->id) {
            InvoiceType::PRODUCTION_TYPE_ID => $this->getProductionTypeInvoice($id),
            default => abort(404),
        };
    }

    private function getProductionTypeInvoice(int $id): Invoice
    {
        $record = Invoice::withBasicPRDProductionTypesRelations()
            ->withBasicPRDProductionTypesRelationCounts()
            ->findOrFail($id);

        $record->appendBasicPRDProductionTypesAttributes();

        return $record;
    }
}
