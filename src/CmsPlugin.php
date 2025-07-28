<?php

namespace Eclipse\Cms;

use Eclipse\Cms\Models\Page;
use Eclipse\Cms\Models\Section;
use Eclipse\Common\Foundation\Plugins\HasLinkables;
use Eclipse\Common\Foundation\Plugins\Plugin;

class CmsPlugin extends Plugin implements HasLinkables
{
    public function getLinkables(): array
    {
        return [
            Page::class => 'Page',
            Section::class => 'Section',
        ];
    }
}
