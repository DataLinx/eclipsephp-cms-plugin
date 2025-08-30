<?php

namespace Eclipse\Cms\Observers;

use Eclipse\Cms\Models\Banner;

class BannerObserver
{
    public function deleting(Banner $banner): void
    {
        $banner->images()->delete();
    }
}
