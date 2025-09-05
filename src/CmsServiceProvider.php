<?php

namespace Eclipse\Cms;

use Eclipse\Cms\Models\Banner\Position;
use Eclipse\Cms\Policies\BannerPositionPolicy;
use Eclipse\Common\Foundation\Providers\PackageServiceProvider;
use Eclipse\Common\Package;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Columns\ImageColumn;
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
        Gate::policy(Position::class, BannerPositionPolicy::class);

        ImageColumn::macro(
            'preview',
            fn (bool $enabled = true) => $enabled ? $this->extraImgAttributes([
                'class' => 'cursor-pointer image-preview-trigger',
                'onclick' => 'event.stopPropagation(); return false;',
            ]) : $this
        );

        FilamentView::registerRenderHook(
            'panels::body.end',
            fn () => view('eclipse-cms::components.image-preview-modal')->render()
        );
    }
}
