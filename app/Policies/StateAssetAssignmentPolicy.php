<?php

namespace App\Policies;

use App\Models\StateAssetAssignment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StateAssetAssignmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, StateAssetAssignment $stateAssetAssignment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, StateAssetAssignment $stateAssetAssignment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, StateAssetAssignment $stateAssetAssignment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, StateAssetAssignment $stateAssetAssignment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, StateAssetAssignment $stateAssetAssignment): bool
    {
        return false;
    }
}
