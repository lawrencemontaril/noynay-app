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

        // Associate the appointment to the patient's user model
        if (auth()->check() && auth()->user()->hasRole('patient')) {
            $appointment->patient_id = auth()->user()->patient->id;
        }
    }
}
