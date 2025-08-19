<?php

namespace Eclipse\Cms\Policies;

use Eclipse\Cms\Models\Menu\Item;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Access\Authorizable;

class MenuItemPolicy
{
    use HandlesAuthorization;

    public function viewAny(Authorizable $user): bool
    {
        return $user->can('view_any_menu::item');
    }

    public function view(Authorizable $user, Item $item): bool
    {
        return $user->can('view_menu::item');
    }

    public function create(Authorizable $user): bool
    {
        return $user->can('create_menu::item');
    }

    public function update(Authorizable $user, Item $item): bool
    {
        return $user->can('update_menu::item');
    }

    public function delete(Authorizable $user, Item $item): bool
    {
        return $user->can('delete_menu::item');
    }

    public function deleteAny(Authorizable $user): bool
    {
        return $user->can('delete_any_menu::item');
    }

    public function forceDelete(Authorizable $user, Item $item): bool
    {
        return $user->can('force_delete_menu::item');
    }

    public function forceDeleteAny(Authorizable $user): bool
    {
        return $user->can('force_delete_any_menu::item');
    }

    public function restore(Authorizable $user, Item $item): bool
    {
        return $user->can('restore_menu::item');
    }

    public function restoreAny(Authorizable $user): bool
    {
        return $user->can('restore_any_menu::item');
    }

    public function replicate(Authorizable $user, Item $item): bool
    {
        return $user->can('replicate_menu::item');
    }

    public function reorder(Authorizable $user): bool
    {
        return $user->can('reorder_menu::item');
    }
}
