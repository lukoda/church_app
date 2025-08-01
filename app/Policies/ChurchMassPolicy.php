<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\ChurchMass;
use App\Models\User;

class ChurchMassPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any ChurchMass');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ChurchMass $churchmass): bool
    {
        return $user->checkPermissionTo('view ChurchMass');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create ChurchMass');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ChurchMass $churchmass): bool
    {
        return $user->checkPermissionTo('update ChurchMass');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ChurchMass $churchmass): bool
    {
        return $user->checkPermissionTo('delete ChurchMass');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ChurchMass $churchmass): bool
    {
        return $user->checkPermissionTo('restore ChurchMass');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ChurchMass $churchmass): bool
    {
        return $user->checkPermissionTo('force-delete ChurchMass');
    }
}
