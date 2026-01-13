<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewProductionTypeInvoiceForPaymentReceived extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(Invoice $invoice)
    {
        // Freeze data at the time of notification creation
        $this->data = [
            'type' => class_basename(static::class),
            'invoice_id' => $invoice->id,
            'order_name' => $invoice->invoiceable->name,
            'products_count' => $invoice->products->count(),
            'order_manufacturer_name' => $invoice->invoiceable->manufacturer->name,
            'order_country_code' => $invoice->invoiceable->country->code,
        ];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
