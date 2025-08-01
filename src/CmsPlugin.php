<?php

namespace Eclipse\Cms;

use Eclipse\Cms\Filament\Resources\PageResource;
use Eclipse\Cms\Filament\Resources\SectionResource;
use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\SpatieLaravelTranslatablePlugin;

class CmsPlugin implements Plugin
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
            ])
            ->plugin(
                SpatieLaravelTranslatablePlugin::make()
                    ->defaultLocales(['en'])
            );
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
