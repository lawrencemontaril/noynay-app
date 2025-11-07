<?php

namespace App\Listeners;

use App\Events\AppointmentCancelledEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogAppointmentCancelled
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
    public function handle(AppointmentCancelledEvent $event): void
    {
        activity('appointment')
            ->performedOn($event->appointment)
            ->causedBy(auth()->user())
            ->log('cancelled appointment');
    }
}
