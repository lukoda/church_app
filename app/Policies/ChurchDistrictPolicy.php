<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\ChurchDistrict;
use App\Models\Admin;
use App\Models\User;

class ChurchDistrictPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User|Admin $user): bool
    {
        return $user->checkPermissionTo('view-any ChurchDistrict');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User|Admin $user, ChurchDistrict $churchdistrict): bool
    {
        return $user->checkPermissionTo('view ChurchDistrict');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User|Admin $user): bool
    {
        return $user->checkPermissionTo('create ChurchDistrict');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User|Admin $user, ChurchDistrict $churchdistrict): bool
    {
        return $user->checkPermissionTo('update ChurchDistrict');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User|Admin $user, ChurchDistrict $churchdistrict): bool
    {
        return $user->checkPermissionTo('delete ChurchDistrict');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User|Admin $user, ChurchDistrict $churchdistrict): bool
    {
        return $user->checkPermissionTo('restore ChurchDistrict');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User|Admin $user, ChurchDistrict $churchdistrict): bool
    {
        return $user->checkPermissionTo('force-delete ChurchDistrict');
    }
}
