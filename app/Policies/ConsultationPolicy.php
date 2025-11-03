<?php

namespace App\Policies;

use App\Models\Consultation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ConsultationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('consultations:view_any');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Consultation $consultation): bool
    {
        return $user->can('consultations:view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('consultations:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Consultation $consultation): bool
    {
        return $user->can('consultations:update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Consultation $consultation): bool
    {
        return $user->can('consultations:delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Consultation $consultation): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Consultation $consultation): bool
    {
        return false;
    }
}
