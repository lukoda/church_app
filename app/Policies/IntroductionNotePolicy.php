<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\IntroductionNote;
use App\Models\User;

class IntroductionNotePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any IntroductionNote');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, IntroductionNote $introductionnote): bool
    {
        return $user->checkPermissionTo('view IntroductionNote');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create IntroductionNote');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, IntroductionNote $introductionnote): bool
    {
        return $user->checkPermissionTo('update IntroductionNote');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, IntroductionNote $introductionnote): bool
    {
        return $user->checkPermissionTo('delete IntroductionNote');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, IntroductionNote $introductionnote): bool
    {
        return $user->checkPermissionTo('restore IntroductionNote');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, IntroductionNote $introductionnote): bool
    {
        return $user->checkPermissionTo('force-delete IntroductionNote');
    }
}
