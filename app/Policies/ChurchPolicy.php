<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Church;
use App\Models\User;
use App\Models\Admin;

class ChurchPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User|Admin $user): bool
    {
        return $user->checkPermissionTo('view-any Church');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User|Admin $user, Church $church): bool
    {
        return $user->checkPermissionTo('view Church');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User|Admin $user): bool
    {
        return $user->checkPermissionTo('create Church');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User|Admin $user, Church $church): bool
    {
        return $user->checkPermissionTo('update Church');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User|Admin $user, Church $church): bool
    {
        return $user->checkPermissionTo('delete Church');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User|Admin $user, Church $church): bool
    {
        return $user->checkPermissionTo('restore Church');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User|Admin $user, Church $church): bool
    {
        return $user->checkPermissionTo('force-delete Church');
    }
}
