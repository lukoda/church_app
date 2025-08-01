<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\LogOffering;
use App\Models\User;

class LogOfferingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any LogOffering');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LogOffering $logoffering): bool
    {
        return $user->checkPermissionTo('view LogOffering');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create LogOffering');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LogOffering $logoffering): bool
    {
        return $user->checkPermissionTo('update LogOffering');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LogOffering $logoffering): bool
    {
        return $user->checkPermissionTo('delete LogOffering');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LogOffering $logoffering): bool
    {
        return $user->checkPermissionTo('restore LogOffering');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LogOffering $logoffering): bool
    {
        return $user->checkPermissionTo('force-delete LogOffering');
    }
}
