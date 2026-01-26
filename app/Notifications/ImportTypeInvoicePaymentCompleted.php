<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImportTypeInvoicePaymentCompleted extends Notification
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
            'shipment_title' => $invoice->invoiceable->title,
            'shipment_products_count' => $invoice->invoiceable->products->count(),
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
