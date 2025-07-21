<?php

namespace Eclipse\PluginTemplate;

use Eclipse\Common\Foundation\Providers\PackageServiceProvider;
use Eclipse\Common\Package;
use Spatie\LaravelPackageTools\Package as SpatiePackage;

class PluginTemplateServiceProvider extends PackageServiceProvider
{
    public static string $name = 'plugin-template';

    public function configurePackage(SpatiePackage|Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasTranslations();
    }
}
