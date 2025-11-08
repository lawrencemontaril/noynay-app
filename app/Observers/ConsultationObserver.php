<?php

namespace App\Observers;

use App\Models\Consultation;
use App\Models\User;
use App\Notifications\ConsultationCreated;
use App\Notifications\PendingInvoice;
use App\Services\AppointmentService;

class ConsultationObserver
{
    public function creating(Consultation $consultation): void
    {
        $appointmentService = app(AppointmentService::class);

        // Send a pending invoice notification to cashier
        if (! $appointmentService->hasBeenServiced($consultation->appointment)) {
            $cashiers = User::role('cashier')->get();

            foreach ($cashiers as $cashier) {
                $cashier->notify(new PendingInvoice($consultation->appointment));
            }
        }
    }

    /**
     * Handle the Consultation "created" event.
     */
    public function created(Consultation $consultation): void
    {
        // Mark appointment as completed
        // if ($consultation->appointment()->exists() && $consultation->appointment->invoice()->exists()) {
        //     $appointment = $consultation->appointment;
        //     $appointment->status = 'completed';
        //     $appointment->save();
        // }

        $user = $consultation->appointment?->patient?->user;

        if ($user) {
            $user->notify(new ConsultationCreated($consultation));
        }
    }

    /**
     * Handle the Consultation "updated" event.
     */
    public function updated(Consultation $consultation): void
    {
        //
    }

    /**
     * Handle the Consultation "deleted" event.
     */
    public function deleted(Consultation $consultation): void
    {
        //
    }

    /**
     * Handle the Consultation "restored" event.
     */
    public function restored(Consultation $consultation): void
    {
        //
    }

    /**
     * Handle the Consultation "force deleted" event.
     */
    public function forceDeleted(Consultation $consultation): void
    {
        //
    }
}
