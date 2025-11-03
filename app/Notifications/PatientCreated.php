<?php

namespace App\Notifications;

use App\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PatientCreated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Patient $patient,
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
        $patient = $this->patient;
        $name = "{$patient->first_name} {$patient->last_name}";

        return [
            'message' => "A new patient named {$name} has been registered.",
            'link' => "/admin/patients?id={$patient->id}",
        ];
    }
}
