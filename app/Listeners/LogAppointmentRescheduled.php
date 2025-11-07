<?php

namespace App\Listeners;

use App\Events\AppointmentRescheduledEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogAppointmentRescheduled
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AppointmentRescheduledEvent $event): void
    {
        activity('appointment')
            ->performedOn($event->appointment)
            ->causedBy(auth()->user())
            ->log('rescheduled appointment');
    }
}
