<?php

namespace Eclipse\Cms;

use Eclipse\Cms\Admin\Filament\Resources\PageResource;
use Eclipse\Cms\Models\Page;
use Eclipse\Cms\Models\Section;
use Eclipse\Common\Foundation\Plugins\HasLinkables;
use Eclipse\Common\Foundation\Plugins\Plugin;
use Exception;
use Filament\Facades\Filament;
use Filament\Forms\Components\MorphToSelect;
use Filament\Navigation\NavigationItem;
use Filament\Panel;

class CmsPlugin extends Plugin implements HasLinkables
{
    public function register(Panel $panel): void
    {
        parent::register($panel);

        $panel->navigationItems($this->getSectionNavigationItems());
    }

    public function getSectionNavigationItems(): array
    {
        try {
            return Section::query()
                ->select('name', 'id', config('eclipse-cms.tenancy.foreign_key'))
                ->get()
                ->map(fn (Section $section): NavigationItem => NavigationItem::make($section->getTranslation('name', app()->getLocale()))
                    ->url(
                        fn (): string => PageResource::getUrl('index', [
                            'sId' => $section->id,
                        ])
                    )
                    ->icon('heroicon-o-document-text')
                    ->group('CMS')
                    ->sort(10)
                    ->visible(fn (): bool => $section->{config('eclipse-cms.tenancy.foreign_key')} === Filament::getTenant()?->id)
                )
                ->toArray();
        } catch (Exception) {
            return [];
        }
    }

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
