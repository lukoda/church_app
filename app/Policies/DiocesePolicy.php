<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Diocese;
use App\Models\User;
use App\Models\Admin;

class DiocesePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User|Admin $user): bool
    {
        return $user->checkPermissionTo('view-any Diocese');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User|Admin $user, Diocese $diocese): bool
    {
        return $user->checkPermissionTo('view Diocese');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User|Admin $user): bool
    {
        return $user->checkPermissionTo('create Diocese');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User|Admin $user, Diocese $diocese): bool
    {
        return $user->checkPermissionTo('update Diocese');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User|Admin $user, Diocese $diocese): bool
    {
        return $user->checkPermissionTo('delete Diocese');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User|Admin $user, Diocese $diocese): bool
    {
        return $user->checkPermissionTo('restore Diocese');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User|Admin $user, Diocese $diocese): bool
    {
        return $user->checkPermissionTo('force-delete Diocese');
    }
}
