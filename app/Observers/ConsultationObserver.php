<?php

namespace App\Observers;

use App\Models\{Consultation, User};
use App\Notifications\{ConsultationCreated, PendingInvoice};
use App\Services\AppointmentService;
use Illuminate\Support\Facades\Notification;

class ConsultationObserver
{
    /**
     * Handle the Consultation "creating" event.
     * 
     * We check if the appointment has been serviced in the "creating" model
     * event so that it checks for other services within this appointment
     * excluding this one.
     */
    public function creating(Consultation $consultation): void
    {
        $appointmentService = app(AppointmentService::class);

        // Send a pending invoice notification to cashier
        if (! $appointmentService->hasBeenServiced($consultation->appointment)) {
            $cashiers = User::role('cashier')->get();

            Notification::send($cashiers, new PendingInvoice($consultation->appointment));
        }
    }

    /**
     * Handle the Consultation "created" event.
     */
    public function created(Consultation $consultation): void
    {
        $consultation->appointment->patient->user?->notify(new ConsultationCreated($consultation));
    }
}
