<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\BeneficiaryRequestItemPledge;
use App\Models\User;

class BeneficiaryRequestItemPledgePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any BeneficiaryRequestItemPledge');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BeneficiaryRequestItemPledge $beneficiaryrequestitempledge): bool
    {
        return $user->checkPermissionTo('view BeneficiaryRequestItemPledge');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create BeneficiaryRequestItemPledge');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BeneficiaryRequestItemPledge $beneficiaryrequestitempledge): bool
    {
        return $user->checkPermissionTo('update BeneficiaryRequestItemPledge');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BeneficiaryRequestItemPledge $beneficiaryrequestitempledge): bool
    {
        return $user->checkPermissionTo('delete BeneficiaryRequestItemPledge');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BeneficiaryRequestItemPledge $beneficiaryrequestitempledge): bool
    {
        return $user->checkPermissionTo('restore BeneficiaryRequestItemPledge');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BeneficiaryRequestItemPledge $beneficiaryrequestitempledge): bool
    {
        return $user->checkPermissionTo('force-delete BeneficiaryRequestItemPledge');
    }
}
