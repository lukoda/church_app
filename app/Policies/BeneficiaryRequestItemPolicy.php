<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\BeneficiaryRequestItem;
use App\Models\User;

class BeneficiaryRequestItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any BeneficiaryRequestItem');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BeneficiaryRequestItem $beneficiaryrequestitem): bool
    {
        return $user->checkPermissionTo('view BeneficiaryRequestItem');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create BeneficiaryRequestItem');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BeneficiaryRequestItem $beneficiaryrequestitem): bool
    {
        return $user->checkPermissionTo('update BeneficiaryRequestItem');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BeneficiaryRequestItem $beneficiaryrequestitem): bool
    {
        return $user->checkPermissionTo('delete BeneficiaryRequestItem');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BeneficiaryRequestItem $beneficiaryrequestitem): bool
    {
        return $user->checkPermissionTo('restore BeneficiaryRequestItem');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BeneficiaryRequestItem $beneficiaryrequestitem): bool
    {
        return $user->checkPermissionTo('force-delete BeneficiaryRequestItem');
    }
}
