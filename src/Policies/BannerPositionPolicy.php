<?php

namespace Eclipse\Cms\Policies;

use Eclipse\Cms\Models\Banner\Position;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Access\Authorizable;

class BannerPositionPolicy
{
    use HandlesAuthorization;

    public function viewAny(Authorizable $user): bool
    {
        return $user->can('view_any_banner::position');
    }

    public function view(Authorizable $user, Position $position): bool
    {
        return $user->can('manage_banners_banner::position');
    }

    public function create(Authorizable $user): bool
    {
        return $user->can('create_banner::position');
    }

    public function update(Authorizable $user, Position $position): bool
    {
        return $user->can('update_banner::position');
    }

    public function delete(Authorizable $user, Position $position): bool
    {
        return $user->can('delete_banner::position');
    }

    public function deleteAny(Authorizable $user): bool
    {
        return $user->can('delete_any_banner::position');
    }

    public function forceDelete(Authorizable $user, Position $position): bool
    {
        return $user->can('force_delete_banner::position');
    }

    public function forceDeleteAny(Authorizable $user): bool
    {
        return $user->can('force_delete_any_banner::position');
    }

    public function restore(Authorizable $user, Position $position): bool
    {
        return $user->can('restore_banner::position');
    }

    public function restoreAny(Authorizable $user): bool
    {
        return $user->can('restore_any_banner::position');
    }
}
