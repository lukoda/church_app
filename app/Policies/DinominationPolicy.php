<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Dinomination;
use App\Models\User;

class DinominationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Dinomination');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Dinomination $dinomination): bool
    {
        return $user->checkPermissionTo('view Dinomination');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Dinomination');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Dinomination $dinomination): bool
    {
        return $user->checkPermissionTo('update Dinomination');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Dinomination $dinomination): bool
    {
        return $user->checkPermissionTo('delete Dinomination');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Dinomination $dinomination): bool
    {
        return $user->checkPermissionTo('restore Dinomination');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Dinomination $dinomination): bool
    {
        return $user->checkPermissionTo('force-delete Dinomination');
    }
}
