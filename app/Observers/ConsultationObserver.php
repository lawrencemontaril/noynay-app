<?php

namespace App\Observers;

use App\Models\{Consultation, User};
use App\Notifications\{ConsultationCreated, PendingInvoice};
use App\Services\AppointmentService;

class ConsultationObserver
{
    /**
     * Handle the Consultation "creating" event.
     */
    public function creating(Consultation $consultation): void
    {
        $appointmentService = app(AppointmentService::class);

        // Send a pending invoice notification to cashier
        if (! $appointmentService->hasBeenServiced($consultation->appointment)) {
            $cashiers = User::role('cashier')->get();

            foreach ($cashiers as $cashier) {
                $cashier?->notify(new PendingInvoice($consultation->appointment));
            }
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
