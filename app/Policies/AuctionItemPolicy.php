<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\AuctionItem;
use App\Models\User;

class AuctionItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any AuctionItem');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AuctionItem $auctionitem): bool
    {
        return $user->checkPermissionTo('view AuctionItem');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create AuctionItem');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AuctionItem $auctionitem): bool
    {
        return $user->checkPermissionTo('update AuctionItem');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AuctionItem $auctionitem): bool
    {
        return $user->checkPermissionTo('delete AuctionItem');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AuctionItem $auctionitem): bool
    {
        return $user->checkPermissionTo('restore AuctionItem');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AuctionItem $auctionitem): bool
    {
        return $user->checkPermissionTo('force-delete AuctionItem');
    }
}
