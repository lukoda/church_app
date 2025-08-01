<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\ChurchSecretary;
use App\Models\User;
use App\Models\Admin;

class ChurchSecretaryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User|Admin $user): bool
    {
        return $user->checkPermissionTo('view-any ChurchSecretary');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User|Admin $user, ChurchSecretary $churchsecretary): bool
    {
        return $user->checkPermissionTo('view ChurchSecretary');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User|Admin $user): bool
    {
        return $user->checkPermissionTo('create ChurchSecretary');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User|Admin $user, ChurchSecretary $churchsecretary): bool
    {
        return $user->checkPermissionTo('update ChurchSecretary');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User|Admin $user, ChurchSecretary $churchsecretary): bool
    {
        return $user->checkPermissionTo('delete ChurchSecretary');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User|Admin $user, ChurchSecretary $churchsecretary): bool
    {
        return $user->checkPermissionTo('restore ChurchSecretary');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User|Admin $user, ChurchSecretary $churchsecretary): bool
    {
        return $user->checkPermissionTo('force-delete ChurchSecretary');
    }
}
