<?php

namespace App\Http\Controllers\PRD;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceType;
use Illuminate\Http\Request;

class PRDInvoiceController extends Controller
{
    /**
     * AJAX request
     */
    public function accept(Invoice $record)
    {
        $record->acceptByPRD();

        // Return refetched updated record
        $record = $this->getRecordByType($record->type, $record->id);

        return $record;
    }

    /**
     * AJAX request
     */
    public function completePayment(Invoice $record)
    {
        $record->completePaymentByPRD();

        // Return refetched updated record
        $record = $this->getRecordByType($record->paymentType, $record->id);

        return $record;
    }

    private function getRecordByType(InvoiceType $invoiceType, $id)
    {
        switch ($invoiceType->id) {
            case InvoiceType::PRODUCTION_TYPE_ID:
                $record = Invoice::withBasicPRDProductionTypesRelations()
                    ->withBasicPRDProductionTypesRelationCounts()
                    ->findOrFail($id);

                $record->appendBasicPRDProductionTypesAttributes();

                return $record;
                break;
        }
    }
}
