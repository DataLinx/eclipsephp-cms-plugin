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
        return $user->can('view_any_position');
    }

    public function view(Authorizable $user, Position $position): bool
    {
        return $user->can('view_position');
    }

    public function create(Authorizable $user): bool
    {
        return $user->can('create_position');
    }

    public function update(Authorizable $user, Position $position): bool
    {
        return $user->can('update_position');
    }

    public function delete(Authorizable $user, Position $position): bool
    {
        return $user->can('delete_position');
    }

    public function deleteAny(Authorizable $user): bool
    {
        return $user->can('delete_any_position');
    }

    public function forceDelete(Authorizable $user, Position $position): bool
    {
        return $user->can('force_delete_position');
    }

    public function forceDeleteAny(Authorizable $user): bool
    {
        return $user->can('force_delete_any_position');
    }

    public function restore(Authorizable $user, Position $position): bool
    {
        return $user->can('restore_position');
    }

    public function restoreAny(Authorizable $user): bool
    {
        return $user->can('restore_any_position');
    }
}
