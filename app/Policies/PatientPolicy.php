<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\{Patient, User};

class PatientPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('patients:view_any');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Patient $patient): bool
    {
        $isOwner = optional($user->patient)->id === $patient->id;

        return $user->can('patients:view')
            || $isOwner;
    }

    /**
     * Determine whether the user can view the model's invoices.
     */
    public function viewInvoice(User $user, Patient $patient): bool
    {
        return $user->can('patients:view')
            && $user->can('invoices:view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('patients:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Patient $patient): bool
    {
        return $user->can('patients:update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Patient $patient): bool
    {
        return $user->can('patients:delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Patient $patient): bool
    {
        return $user->can('patients:restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Patient $patient): bool
    {
        return $user->can('patients:force_delete');
    }
}
