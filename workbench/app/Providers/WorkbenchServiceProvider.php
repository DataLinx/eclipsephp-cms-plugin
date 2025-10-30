<?php

namespace Workbench\App\Providers;

use BezhanSalleh\FilamentShield\FilamentShieldServiceProvider;
use Eclipse\Common\CommonServiceProvider;
use Filament\FilamentServiceProvider;
use Illuminate\Support\ServiceProvider;
use Livewire\LivewireServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

class WorkbenchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(PermissionServiceProvider::class);
        $this->app->register(FilamentShieldServiceProvider::class);
        $this->app->register(LivewireServiceProvider::class);
        $this->app->register(FilamentServiceProvider::class);
        $this->app->register(CommonServiceProvider::class);
        $this->app->register(AdminPanelProvider::class);
        $this->app->register(AuthServiceProvider::class);
    }

    public function boot(): void {}
}
