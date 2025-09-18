<?php

namespace Workbench\App\Providers;

use Eclipse\Common\CommonServiceProvider;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Support\ServiceProvider;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->register(CommonServiceProvider::class);
        $this->app->register(AdminPanelProvider::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Minimal preview macro for tests only
        if (! ImageColumn::hasMacro('preview')) {
            ImageColumn::macro('preview', fn () => $this);
        }
    }
}
