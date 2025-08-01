<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\Jumuiya;
use App\Models\User;

class JumuiyaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any Jumuiya');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Jumuiya $jumuiya): bool
    {
        return $user->checkPermissionTo('view Jumuiya');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create Jumuiya');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Jumuiya $jumuiya): bool
    {
        return $user->checkPermissionTo('update Jumuiya');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Jumuiya $jumuiya): bool
    {
        return $user->checkPermissionTo('delete Jumuiya');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Jumuiya $jumuiya): bool
    {
        return $user->checkPermissionTo('restore Jumuiya');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Jumuiya $jumuiya): bool
    {
        return $user->checkPermissionTo('force-delete Jumuiya');
    }
}
