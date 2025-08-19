<?php

namespace Eclipse\Cms;

use Eclipse\Cms\Models\Menu;
use Eclipse\Cms\Models\Menu\Item;
use Eclipse\Cms\Policies\MenuItemPolicy;
use Eclipse\Cms\Policies\MenuPolicy;
use Eclipse\Common\Foundation\Providers\PackageServiceProvider;
use Eclipse\Common\Package;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPackageTools\Package as SpatiePackage;

class CmsServiceProvider extends PackageServiceProvider
{
    public static string $name = 'eclipse-cms';

    public function configurePackage(SpatiePackage|Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->discoversMigrations()
            ->runsMigrations();
    }

    public function bootingPackage(): void
    {
        Gate::policy(Menu::class, MenuPolicy::class);
        Gate::policy(Item::class, MenuItemPolicy::class);
    }
}
