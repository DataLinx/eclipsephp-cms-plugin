<?php

namespace Eclipse\Cms\Policies;

use Eclipse\Cms\Models\Menu;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Access\Authorizable;

class MenuPolicy
{
    use HandlesAuthorization;

    public function viewAny(Authorizable $user): bool
    {
        return $user->can('view_any_menu');
    }

    public function create(Authorizable $user): bool
    {
        return $user->can('create_menu');
    }

    public function update(Authorizable $user, Menu $menu): bool
    {
        return $user->can('update_menu');
    }

    public function delete(Authorizable $user, Menu $menu): bool
    {
        return $user->can('delete_menu');
    }

    public function deleteAny(Authorizable $user): bool
    {
        return $user->can('delete_any_menu');
    }

    public function forceDelete(Authorizable $user, Menu $menu): bool
    {
        return $user->can('force_delete_menu');
    }

    public function forceDeleteAny(Authorizable $user): bool
    {
        return $user->can('force_delete_any_menu');
    }

    public function restore(Authorizable $user, Menu $menu): bool
    {
        return $user->can('restore_menu');
    }

    public function restoreAny(Authorizable $user): bool
    {
        return $user->can('restore_any_menu');
    }
}
