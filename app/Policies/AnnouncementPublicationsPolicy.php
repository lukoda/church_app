<?php

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\AnnouncementPublications;
use App\Models\User;

class AnnouncementPublicationsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo('view-any AnnouncementPublications');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AnnouncementPublications $announcementpublications): bool
    {
        return $user->checkPermissionTo('view AnnouncementPublications');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo('create AnnouncementPublications');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AnnouncementPublications $announcementpublications): bool
    {
        return $user->checkPermissionTo('update AnnouncementPublications');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AnnouncementPublications $announcementpublications): bool
    {
        return $user->checkPermissionTo('delete AnnouncementPublications');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AnnouncementPublications $announcementpublications): bool
    {
        return $user->checkPermissionTo('restore AnnouncementPublications');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AnnouncementPublications $announcementpublications): bool
    {
        return $user->checkPermissionTo('force-delete AnnouncementPublications');
    }
}
