<?php

namespace App\Notifications;

use App\Models\LaboratoryResult;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LaboratoryResultCreated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected LaboratoryResult $laboratoryResult
    ) {
        //
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
        $appointment = $this->laboratoryResult->appointment;

        return [
            'message' => "Your laboratory result for {$appointment->type_label} is now available.",
            'link' => "/laboratory_results?id={$this->laboratoryResult->id}",
        ];
    }
}
