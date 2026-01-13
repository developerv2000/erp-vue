<?php

namespace App\Notifications;

use App\Models\Process;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProcessMarkedAsReadyForOrder extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(Process $process)
    {
        // Freeze data at the time of notification creation
        $this->data = [
            'type' => class_basename(static::class),
            'process_id' => $process->id,
            'full_english_product_label' => $process->full_english_product_label,
            'manufacturer' => $process->product->manufacturer->name,
            'country' => $process->searchCountry->code,
            'marketing_authorization_holder' => $process->MAH?->name,
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
