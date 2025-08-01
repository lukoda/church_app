<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\ChurchAuction;
use App\Models\User;

class ChurchAuctionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any ChurchAuction');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ChurchAuction $churchauction): bool
    {
        return $user->checkPermissionTo('view ChurchAuction');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create ChurchAuction');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ChurchAuction $churchauction): bool
    {
        return $user->checkPermissionTo('update ChurchAuction');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ChurchAuction $churchauction): bool
    {
        return $user->checkPermissionTo('delete ChurchAuction');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ChurchAuction $churchauction): bool
    {
        return $user->checkPermissionTo('restore ChurchAuction');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ChurchAuction $churchauction): bool
    {
        return $user->checkPermissionTo('force-delete ChurchAuction');
    }
}
