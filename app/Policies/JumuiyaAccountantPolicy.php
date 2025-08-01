<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\JumuiyaAccountant;
use App\Models\User;

class JumuiyaAccountantPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any JumuiyaAccountant');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, JumuiyaAccountant $jumuiyaaccountant): bool
    {
        return $user->checkPermissionTo('view JumuiyaAccountant');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create JumuiyaAccountant');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, JumuiyaAccountant $jumuiyaaccountant): bool
    {
        return $user->checkPermissionTo('update JumuiyaAccountant');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, JumuiyaAccountant $jumuiyaaccountant): bool
    {
        return $user->checkPermissionTo('delete JumuiyaAccountant');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, JumuiyaAccountant $jumuiyaaccountant): bool
    {
        return $user->checkPermissionTo('restore JumuiyaAccountant');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, JumuiyaAccountant $jumuiyaaccountant): bool
    {
        return $user->checkPermissionTo('force-delete JumuiyaAccountant');
    }
}
