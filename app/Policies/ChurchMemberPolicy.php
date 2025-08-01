<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\ChurchMember;
use App\Models\User;

class ChurchMemberPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any ChurchMember');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ChurchMember $churchmember): bool
    {
        return $user->checkPermissionTo('view ChurchMember');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create ChurchMember');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ChurchMember $churchmember): bool
    {
        return $user->checkPermissionTo('update ChurchMember');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ChurchMember $churchmember): bool
    {
        return $user->checkPermissionTo('delete ChurchMember');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ChurchMember $churchmember): bool
    {
        return $user->checkPermissionTo('restore ChurchMember');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ChurchMember $churchmember): bool
    {
        return $user->checkPermissionTo('force-delete ChurchMember');
    }
}
