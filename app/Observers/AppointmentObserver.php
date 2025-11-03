<?php

namespace App\Observers;

use App\Models\Appointment;

class AppointmentObserver
{
    /**
     * Handle the Appointment "creating" event.
     */
    public function creating(Appointment $appointment): void
    {
        if ($appointment->patient_id) {
            return;
        }

        // Otherwise, if the authenticated user is a patient, assign it automatically
        if (auth()->check() && auth()->user()->hasRole('patient')) {
            $appointment->patient_id = auth()->user()->patient->id;
        }
    }
}
