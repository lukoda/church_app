<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Dependant;
use App\Models\User;

class DependantPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Dependant');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Dependant $dependant): bool
    {
        return $user->checkPermissionTo('view Dependant');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Dependant');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Dependant $dependant): bool
    {
        return $user->checkPermissionTo('update Dependant');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Dependant $dependant): bool
    {
        return $user->checkPermissionTo('delete Dependant');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Dependant $dependant): bool
    {
        return $user->checkPermissionTo('restore Dependant');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Dependant $dependant): bool
    {
        return $user->checkPermissionTo('force-delete Dependant');
    }
}
