<?php

namespace Eclipse\Cms;

use Eclipse\Common\Foundation\Plugins\Plugin;
use Filament\Panel;

class CmsPlugin extends Plugin
{
    public function register(Panel $panel): void
    {
        parent::register($panel);
    }
}
