<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AppointmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('appointments:view_any');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Appointment $appointment): bool
    {
        $isOwner = optional($user->patient)->id === $appointment->patient_id;

        return $user->can('appointments:view')
            || $isOwner;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('appointments:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Appointment $appointment): bool
    {
        $isOwner = optional($user->patient)->id === $appointment->patient_id;

        return $user->can('appointments:update')
            || $isOwner;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->can('appointments:delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Appointment $appointment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Appointment $appointment): bool
    {
        return false;
    }
}
