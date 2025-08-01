<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\ChurchServiceRequest;
use App\Models\User;

class ChurchServiceRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any ChurchServiceRequest');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ChurchServiceRequest $churchservicerequest): bool
    {
        return $user->checkPermissionTo('view ChurchServiceRequest');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create ChurchServiceRequest');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ChurchServiceRequest $churchservicerequest): bool
    {
        return $user->checkPermissionTo('update ChurchServiceRequest');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ChurchServiceRequest $churchservicerequest): bool
    {
        return $user->checkPermissionTo('delete ChurchServiceRequest');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ChurchServiceRequest $churchservicerequest): bool
    {
        return $user->checkPermissionTo('restore ChurchServiceRequest');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ChurchServiceRequest $churchservicerequest): bool
    {
        return $user->checkPermissionTo('force-delete ChurchServiceRequest');
    }
}
