<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\ChurchService;
use App\Models\User;

class ChurchServicePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any ChurchService');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ChurchService $churchservice): bool
    {
        return $user->checkPermissionTo('view ChurchService');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create ChurchService');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ChurchService $churchservice): bool
    {
        return $user->checkPermissionTo('update ChurchService');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ChurchService $churchservice): bool
    {
        return $user->checkPermissionTo('delete ChurchService');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ChurchService $churchservice): bool
    {
        return $user->checkPermissionTo('restore ChurchService');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ChurchService $churchservice): bool
    {
        return $user->checkPermissionTo('force-delete ChurchService');
    }
}
