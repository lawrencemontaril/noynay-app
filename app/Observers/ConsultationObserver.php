<?php

namespace App\Observers;

use App\Models\Consultation;
use App\Notifications\ConsultationCreated;

class ConsultationObserver
{
    /**
     * Handle the Consultation "created" event.
     */
    public function created(Consultation $consultation): void
    {
        if ($consultation->appointment()->exists()) {
            $appointment = $consultation->appointment;
            $appointment->status = 'completed';
            $appointment->save();
        }

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
