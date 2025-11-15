<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Appointment;
use Illuminate\Notifications\Messages\MailMessage;

class AppointmentNoShow extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected Appointment $appointment,
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
        $patient = $this->appointment->patient;
        $user = $patient->user;

        return [
            'message' => "{$user->first_name} {$user->last_name} did not show up for their {$this->appointment->type->label()} appointment",
            'link' => "/admin/appointments?id={$this->appointment->id}",
        ];
    }
}
