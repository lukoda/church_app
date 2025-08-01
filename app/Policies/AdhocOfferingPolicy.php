<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\AdhocOffering;
use App\Models\User;

class AdhocOfferingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any AdhocOffering');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AdhocOffering $adhocoffering): bool
    {
        return $user->checkPermissionTo('view AdhocOffering');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create AdhocOffering');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AdhocOffering $adhocoffering): bool
    {
        return $user->checkPermissionTo('update AdhocOffering');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AdhocOffering $adhocoffering): bool
    {
        return $user->checkPermissionTo('delete AdhocOffering');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AdhocOffering $adhocoffering): bool
    {
        return $user->checkPermissionTo('restore AdhocOffering');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AdhocOffering $adhocoffering): bool
    {
        return $user->checkPermissionTo('force-delete AdhocOffering');
    }
}
