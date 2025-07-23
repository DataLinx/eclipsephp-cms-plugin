<?php

namespace Eclipse\Cms;

use Eclipse\Common\Foundation\Providers\PackageServiceProvider;
use Eclipse\Common\Package;
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
}
