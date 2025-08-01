<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\PledgePayment;
use App\Models\User;

class PledgePaymentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any PledgePayment');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PledgePayment $pledgepayment): bool
    {
        return $user->checkPermissionTo('view PledgePayment');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create PledgePayment');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PledgePayment $pledgepayment): bool
    {
        return $user->checkPermissionTo('update PledgePayment');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PledgePayment $pledgepayment): bool
    {
        return $user->checkPermissionTo('delete PledgePayment');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PledgePayment $pledgepayment): bool
    {
        return $user->checkPermissionTo('restore PledgePayment');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PledgePayment $pledgepayment): bool
    {
        return $user->checkPermissionTo('force-delete PledgePayment');
    }
}
