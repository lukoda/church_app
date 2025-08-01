<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\AuctionItemPayment;
use App\Models\User;

class AuctionItemPaymentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any AuctionItemPayment');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AuctionItemPayment $auctionitempayment): bool
    {
        return $user->checkPermissionTo('view AuctionItemPayment');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create AuctionItemPayment');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AuctionItemPayment $auctionitempayment): bool
    {
        return $user->checkPermissionTo('update AuctionItemPayment');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AuctionItemPayment $auctionitempayment): bool
    {
        return $user->checkPermissionTo('delete AuctionItemPayment');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AuctionItemPayment $auctionitempayment): bool
    {
        return $user->checkPermissionTo('restore AuctionItemPayment');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AuctionItemPayment $auctionitempayment): bool
    {
        return $user->checkPermissionTo('force-delete AuctionItemPayment');
    }
}
