<?php

namespace Eclipse\Cms;

use Eclipse\Cms\Models\Page;
use Eclipse\Cms\Models\Section;
use Eclipse\Common\Foundation\Plugins\HasLinkables;
use Eclipse\Common\Foundation\Plugins\Plugin;
use Filament\Forms\Components\MorphToSelect;

class CmsPlugin extends Plugin implements HasLinkables
{
    public function getLinkables(): array
    {
        return [
            MorphToSelect\Type::make(Page::class)
                ->titleAttribute('title')
                ->label('Page'),
            MorphToSelect\Type::make(Section::class)
                ->titleAttribute('name')
                ->label('Section'),
        ];
    }
}
