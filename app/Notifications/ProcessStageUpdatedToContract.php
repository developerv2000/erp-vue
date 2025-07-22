<?php

namespace App\Notifications;

use App\Models\Process;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProcessStageUpdatedToContract extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(Process $process, $statusName)
    {
        $this->process = $process;
        $this->status_name = $statusName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'process_id' => $this->process->id,
            'status_name' => $this->status_name,
        ];
    }
}
