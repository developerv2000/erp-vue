<?php

namespace App\Notifications;

use App\Models\Process;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProcessStageChangedToContract extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(Process $process, $statusName)
    {
        // Freeze data at the time of notification creation
        $this->data = [
            'type' => class_basename(static::class),
            'process_id' => $process->id,
            'status_name' => $statusName,
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
