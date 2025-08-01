<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\PastorSchedule;
use App\Models\User;

class PastorSchedulePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any PastorSchedule');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PastorSchedule $pastorschedule): bool
    {
        return $user->checkPermissionTo('view PastorSchedule');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create PastorSchedule');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PastorSchedule $pastorschedule): bool
    {
        return $user->checkPermissionTo('update PastorSchedule');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PastorSchedule $pastorschedule): bool
    {
        return $user->checkPermissionTo('delete PastorSchedule');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PastorSchedule $pastorschedule): bool
    {
        return $user->checkPermissionTo('restore PastorSchedule');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PastorSchedule $pastorschedule): bool
    {
        return $user->checkPermissionTo('force-delete PastorSchedule');
    }
}
