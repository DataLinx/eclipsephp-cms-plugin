<?php

namespace Eclipse\Cms\Observers;

use Eclipse\Cms\Models\Banner\Position;

class PositionObserver
{
    public function deleting(Position $position): void
    {
        $position->banners()->delete();
    }
}
