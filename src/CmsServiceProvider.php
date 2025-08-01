<?php

namespace Eclipse\Cms;

use Eclipse\Cms\Models\Page;
use Eclipse\Cms\Models\Section;
use Eclipse\Cms\Policies\PagePolicy;
use Eclipse\Cms\Policies\SectionPolicy;
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
            ->hasViews()
            ->discoversMigrations()
            ->runsMigrations();
    }

    public function bootingPackage(): void
    {
        // Register policies
        Gate::policy(Section::class, SectionPolicy::class);
        Gate::policy(Page::class, PagePolicy::class);
    }
}
