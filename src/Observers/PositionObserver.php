<?php

namespace Eclipse\Cms\Observers;

use Eclipse\Cms\Models\Banner\Position;
use Illuminate\Database\Eloquent\Model;

class PositionObserver
{
    public function deleting(Position $position): void
    {
        $position->banners()->each(function (Model $banner): void {
            $banner->delete();
        });
    }
}
