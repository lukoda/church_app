<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Pastor;
use App\Models\User;
use App\Models\Admin;

class PastorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User|Admin $user): bool
    {
        return $user->checkPermissionTo('view-any Pastor');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User|Admin $user, Pastor $pastor): bool
    {
        return $user->checkPermissionTo('view Pastor');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User|Admin $user): bool
    {
        return $user->checkPermissionTo('create Pastor');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User|Admin $user, Pastor $pastor): bool
    {
        return $user->checkPermissionTo('update Pastor');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User|Admin $user, Pastor $pastor): bool
    {
        return $user->checkPermissionTo('delete Pastor');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User|Admin $user, Pastor $pastor): bool
    {
        return $user->checkPermissionTo('restore Pastor');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User|Admin $user, Pastor $pastor): bool
    {
        return $user->checkPermissionTo('force-delete Pastor');
    }
}
