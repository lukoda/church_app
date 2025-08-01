<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\CardPledge;
use App\Models\User;

class CardPledgePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any CardPledge');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CardPledge $cardpledge): bool
    {
        return $user->checkPermissionTo('view CardPledge');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create CardPledge');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CardPledge $cardpledge): bool
    {
        return $user->checkPermissionTo('update CardPledge');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CardPledge $cardpledge): bool
    {
        return $user->checkPermissionTo('delete CardPledge');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CardPledge $cardpledge): bool
    {
        return $user->checkPermissionTo('restore CardPledge');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CardPledge $cardpledge): bool
    {
        return $user->checkPermissionTo('force-delete CardPledge');
    }
}
