<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Ward;
use App\Models\User;

class WardPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Ward');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ward $ward): bool
    {
        return $user->checkPermissionTo('view Ward');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Ward');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ward $ward): bool
    {
        return $user->checkPermissionTo('update Ward');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ward $ward): bool
    {
        return $user->checkPermissionTo('delete Ward');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ward $ward): bool
    {
        return $user->checkPermissionTo('restore Ward');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ward $ward): bool
    {
        return $user->checkPermissionTo('force-delete Ward');
    }
}
