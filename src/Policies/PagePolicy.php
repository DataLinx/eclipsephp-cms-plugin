<?php

namespace Eclipse\Cms\Policies;

use Eclipse\Cms\Models\Page;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Access\Authorizable;

class PagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Authorizable $user): bool
    {
        return $user->can('view_any_page');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Authorizable $user): bool
    {
        return $user->can('create_page');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Authorizable $user, Page $page): bool
    {
        return $user->can('update_page');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Authorizable $user, Page $page): bool
    {
        return $user->can('delete_page');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(Authorizable $user): bool
    {
        return $user->can('delete_any_page');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(Authorizable $user, Page $page): bool
    {
        return $user->can('force_delete_page');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(Authorizable $user): bool
    {
        return $user->can('force_delete_any_page');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(Authorizable $user, Page $page): bool
    {
        return $user->can('restore_page');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(Authorizable $user): bool
    {
        return $user->can('restore_any_page');
    }
}
