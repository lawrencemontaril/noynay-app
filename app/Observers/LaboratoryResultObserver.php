<?php

namespace App\Observers;

use App\Models\LaboratoryResult;
use App\Notifications\LaboratoryResultCreated;

class LaboratoryResultObserver
{
    /**
     * Handle the LaboratoryResult "saving" event.
     */
    public function saving(LaboratoryResult $laboratoryResult): void
    {
        $user = $laboratoryResult->appointment?->patient?->user;

        if (! is_null($laboratoryResult->results_file_path)) {
            $laboratoryResult->status = 'released';

            $user->notify(new LaboratoryResultCreated($laboratoryResult));

            if ($laboratoryResult->appointment()->exists()) {
                $appointment = $laboratoryResult->appointment;
                $appointment->status = 'completed';
                $appointment->save();
            }
        } else {
            $laboratoryResult->status = 'pending';
        }
    }

    /**
     * Handle the LaboratoryResult "created" event.
     */
    public function created(LaboratoryResult $laboratoryResult): void
    {

    }

    /**
     * Handle the LaboratoryResult "updated" event.
     */
    public function updated(LaboratoryResult $laboratoryResult): void
    {
        //
    }

    /**
     * Handle the LaboratoryResult "deleted" event.
     */
    public function deleted(LaboratoryResult $laboratoryResult): void
    {
        //
    }

    /**
     * Handle the LaboratoryResult "restored" event.
     */
    public function restored(LaboratoryResult $laboratoryResult): void
    {
        //
    }

    /**
     * Handle the LaboratoryResult "force deleted" event.
     */
    public function forceDeleted(LaboratoryResult $laboratoryResult): void
    {
        //
    }
}
