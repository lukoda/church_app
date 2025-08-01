<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Committee;
use App\Models\User;

class CommitteePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Committee');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Committee $committee): bool
    {
        return $user->checkPermissionTo('view Committee');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Committee');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Committee $committee): bool
    {
        return $user->checkPermissionTo('update Committee');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Committee $committee): bool
    {
        return $user->checkPermissionTo('delete Committee');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Committee $committee): bool
    {
        return $user->checkPermissionTo('restore Committee');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Committee $committee): bool
    {
        return $user->checkPermissionTo('force-delete Committee');
    }
}
