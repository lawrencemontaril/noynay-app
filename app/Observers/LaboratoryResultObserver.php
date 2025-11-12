<?php

namespace App\Observers;

use App\Enums\LaboratoryResultStatus;
use App\Models\{LaboratoryResult, User};
use App\Notifications\{LaboratoryResultCreated, PendingInvoice};
use App\Services\AppointmentService;

class LaboratoryResultObserver
{
    /**
     * Handle the LaboratoryResult "saving" event.
     */
    public function saving(LaboratoryResult $laboratoryResult): void
    {
        $user = $laboratoryResult->appointment->patient->user;

        if ($laboratoryResult->is_released) {
            $laboratoryResult->status = LaboratoryResultStatus::RELEASED;

            $user?->notify(new LaboratoryResultCreated($laboratoryResult));

            // Send a pending invoice notification to cashier
            if (! app(AppointmentService::class)->hasBeenServiced($laboratoryResult->appointment)) {
                $cashiers = User::role('cashier')->get();

                foreach ($cashiers as $cashier) {
                    $cashier?->notify(new PendingInvoice($laboratoryResult->appointment));
                }
            }
        } else {
            $laboratoryResult->status = LaboratoryResultStatus::PENDING;
        }
    }
}
