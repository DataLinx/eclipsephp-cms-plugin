<?php

namespace Eclipse\Cms\Policies;

use Eclipse\Cms\Models\Section;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Access\Authorizable;

class SectionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Authorizable $user): bool
    {
        return $user->can('view_any_section');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Authorizable $user, Section $section): bool
    {
        return $user->can('view_section');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Authorizable $user): bool
    {
        return $user->can('create_section');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Authorizable $user, Section $section): bool
    {
        return $user->can('update_section');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Authorizable $user, Section $section): bool
    {
        return $user->can('delete_section');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(Authorizable $user): bool
    {
        return $user->can('delete_any_section');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(Authorizable $user, Section $section): bool
    {
        return $user->can('force_delete_section');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(Authorizable $user): bool
    {
        return $user->can('force_delete_any_section');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(Authorizable $user, Section $section): bool
    {
        return $user->can('restore_section');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(Authorizable $user): bool
    {
        return $user->can('restore_any_section');
    }
}
