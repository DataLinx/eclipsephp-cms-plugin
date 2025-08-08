<?php

namespace Eclipse\Cms;

use Eclipse\Cms\Admin\Filament\Resources\PageResource;
use Eclipse\Cms\Admin\Filament\Resources\SectionResource;
use Eclipse\Common\Foundation\Plugins\Plugin;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;

class CmsPlugin extends Plugin
{
    public function getId(): string
    {
        return 'eclipse-cms';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                SectionResource::class,
                PageResource::class,
            ])
            ->navigationGroups([
                NavigationGroup::make('CMS')
                    ->label('CMS')
                    ->collapsible(),
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
