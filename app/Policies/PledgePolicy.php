<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Pledge;
use App\Models\User;

class PledgePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Pledge');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Pledge $pledge): bool
    {
        return $user->checkPermissionTo('view Pledge');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Pledge');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pledge $pledge): bool
    {
        return $user->checkPermissionTo('update Pledge');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pledge $pledge): bool
    {
        return $user->checkPermissionTo('delete Pledge');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Pledge $pledge): bool
    {
        return $user->checkPermissionTo('restore Pledge');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Pledge $pledge): bool
    {
        return $user->checkPermissionTo('force-delete Pledge');
    }
}
