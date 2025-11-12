<?php

namespace App\Observers;

use App\Models\{Patient, User};
use App\Notifications\PatientCreated;

class PatientObserver
{
    /**
     * Handle the Patient "created" event.
     */
    public function created(Patient $patient): void
    {
        // Notify all system administrators
        $admins = User::role('system_admin')->get();

        foreach ($admins as $admin) {
            $admin?->notify(new PatientCreated($patient));
        }
    }
}
