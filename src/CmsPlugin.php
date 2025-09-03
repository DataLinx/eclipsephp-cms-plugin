<?php

namespace Eclipse\Cms;

use Eclipse\Cms\Admin\Filament\Resources\PageResource;
use Eclipse\Cms\Models\Section;
use Eclipse\Common\Foundation\Plugins\Plugin;
use Exception;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Filament\Panel;

class CmsPlugin extends Plugin
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
                            'section' => $section->id,
                        ])
                    )
                    ->icon('heroicon-o-arrow-turn-down-right')
                    ->group('CMS')
                    ->sort(10)
                    ->visible(fn (): bool => $section->{config('eclipse-cms.tenancy.foreign_key')} === Filament::getTenant()?->id)
                )
                ->toArray();
        } catch (Exception) {
            return [];
        }
    }
}
