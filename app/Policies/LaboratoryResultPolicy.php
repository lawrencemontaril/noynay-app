<?php

namespace App\Policies;

use App\Models\LaboratoryResult;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LaboratoryResultPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('laboratory_results:view_any');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LaboratoryResult $laboratoryResult): bool
    {
        return $user->can('laboratory_results:view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('laboratory_results:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LaboratoryResult $laboratoryResult): bool
    {
        return $user->can('laboratory_results:update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LaboratoryResult $laboratoryResult): bool
    {
        return $user->can('laboratory_results:delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LaboratoryResult $laboratoryResult): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LaboratoryResult $laboratoryResult): bool
    {
        return false;
    }
}
