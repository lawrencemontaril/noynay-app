<?php

namespace App\Observers;

use App\Models\Patient;
use App\Models\User;
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
            $admin->notify(new PatientCreated($patient));
        }
    }

    /**
     * Handle the Patient "updated" event.
     */
    public function updated(Patient $patient): void
    {
        //
    }

    /**
     * Handle the Patient "saving" event.
     */
    public function saving(Patient $patient): void
    {
        //
    }

    /**
     * Handle the Patient "deleted" event.
     */
    public function deleted(Patient $patient): void
    {
        //
    }

    /**
     * Handle the Patient "restored" event.
     */
    public function restored(Patient $patient): void
    {
        //
    }

    /**
     * Handle the Patient "force deleted" event.
     */
    public function forceDeleted(Patient $patient): void
    {
        //
    }
}
