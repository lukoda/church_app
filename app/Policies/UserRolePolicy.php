<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\UserRole;
use App\Models\User;

class UserRolePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any UserRole');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UserRole $userrole): bool
    {
        return $user->checkPermissionTo('view UserRole');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create UserRole');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UserRole $userrole): bool
    {
        return $user->checkPermissionTo('update UserRole');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UserRole $userrole): bool
    {
        return $user->checkPermissionTo('delete UserRole');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, UserRole $userrole): bool
    {
        return $user->checkPermissionTo('restore UserRole');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, UserRole $userrole): bool
    {
        return $user->checkPermissionTo('force-delete UserRole');
    }
}
