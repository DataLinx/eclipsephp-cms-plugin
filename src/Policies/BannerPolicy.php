<?php

namespace Eclipse\Cms\Policies;

use Eclipse\Cms\Models\Banner;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Access\Authorizable;

class BannerPolicy
{
    use HandlesAuthorization;

    public function viewAny(Authorizable $user): bool
    {
        return $user->can('view_any_banner');
    }

    public function view(Authorizable $user, Banner $banner): bool
    {
        return $user->can('view_banner');
    }

    public function create(Authorizable $user): bool
    {
        return $user->can('create_banner');
    }

    public function update(Authorizable $user, Banner $banner): bool
    {
        return $user->can('update_banner');
    }

    public function delete(Authorizable $user, Banner $banner): bool
    {
        return $user->can('delete_banner');
    }

    public function deleteAny(Authorizable $user): bool
    {
        return $user->can('delete_any_banner');
    }

    public function forceDelete(Authorizable $user, Banner $banner): bool
    {
        return $user->can('force_delete_banner');
    }

    public function forceDeleteAny(Authorizable $user): bool
    {
        return $user->can('force_delete_any_banner');
    }

    public function restore(Authorizable $user, Banner $banner): bool
    {
        return $user->can('restore_banner');
    }

    public function restoreAny(Authorizable $user): bool
    {
        return $user->can('restore_any_banner');
    }
}
