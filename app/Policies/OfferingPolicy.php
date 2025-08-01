<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Offering;
use App\Models\User;

class OfferingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Offering');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Offering $offering): bool
    {
        return $user->checkPermissionTo('view Offering');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Offering');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Offering $offering): bool
    {
        return $user->checkPermissionTo('update Offering');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Offering $offering): bool
    {
        return $user->checkPermissionTo('delete Offering');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Offering $offering): bool
    {
        return $user->checkPermissionTo('restore Offering');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Offering $offering): bool
    {
        return $user->checkPermissionTo('force-delete Offering');
    }
}
