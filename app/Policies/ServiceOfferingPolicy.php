<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\ServiceOffering;
use App\Models\User;

class ServiceOfferingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any ServiceOffering');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ServiceOffering $serviceoffering): bool
    {
        return $user->checkPermissionTo('view ServiceOffering');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create ServiceOffering');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ServiceOffering $serviceoffering): bool
    {
        return $user->checkPermissionTo('update ServiceOffering');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ServiceOffering $serviceoffering): bool
    {
        return $user->checkPermissionTo('delete ServiceOffering');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ServiceOffering $serviceoffering): bool
    {
        return $user->checkPermissionTo('restore ServiceOffering');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ServiceOffering $serviceoffering): bool
    {
        return $user->checkPermissionTo('force-delete ServiceOffering');
    }
}
