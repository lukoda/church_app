<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Beneficiary;
use App\Models\User;

class BeneficiaryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Beneficiary');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Beneficiary $beneficiary): bool
    {
        return $user->checkPermissionTo('view Beneficiary');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Beneficiary');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Beneficiary $beneficiary): bool
    {
        return $user->checkPermissionTo('update Beneficiary');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Beneficiary $beneficiary): bool
    {
        return $user->checkPermissionTo('delete Beneficiary');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Beneficiary $beneficiary): bool
    {
        return $user->checkPermissionTo('restore Beneficiary');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Beneficiary $beneficiary): bool
    {
        return $user->checkPermissionTo('force-delete Beneficiary');
    }
}
