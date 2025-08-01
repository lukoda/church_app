<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\JumuiyaRevenue;
use App\Models\User;

class JumuiyaRevenuePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any JumuiyaRevenue');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, JumuiyaRevenue $jumuiyarevenue): bool
    {
        return $user->checkPermissionTo('view JumuiyaRevenue');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create JumuiyaRevenue');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, JumuiyaRevenue $jumuiyarevenue): bool
    {
        return $user->checkPermissionTo('update JumuiyaRevenue');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, JumuiyaRevenue $jumuiyarevenue): bool
    {
        return $user->checkPermissionTo('delete JumuiyaRevenue');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, JumuiyaRevenue $jumuiyarevenue): bool
    {
        return $user->checkPermissionTo('restore JumuiyaRevenue');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, JumuiyaRevenue $jumuiyarevenue): bool
    {
        return $user->checkPermissionTo('force-delete JumuiyaRevenue');
    }
}
