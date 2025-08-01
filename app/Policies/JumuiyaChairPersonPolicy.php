<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\JumuiyaChairPerson;
use App\Models\User;

class JumuiyaChairPersonPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any JumuiyaChairPerson');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, JumuiyaChairPerson $jumuiyachairperson): bool
    {
        return $user->checkPermissionTo('view JumuiyaChairPerson');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create JumuiyaChairPerson');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, JumuiyaChairPerson $jumuiyachairperson): bool
    {
        return $user->checkPermissionTo('update JumuiyaChairPerson');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, JumuiyaChairPerson $jumuiyachairperson): bool
    {
        return $user->checkPermissionTo('delete JumuiyaChairPerson');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, JumuiyaChairPerson $jumuiyachairperson): bool
    {
        return $user->checkPermissionTo('restore JumuiyaChairPerson');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, JumuiyaChairPerson $jumuiyachairperson): bool
    {
        return $user->checkPermissionTo('force-delete JumuiyaChairPerson');
    }
}
