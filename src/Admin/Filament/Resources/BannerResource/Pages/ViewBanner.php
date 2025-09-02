<?php

namespace Eclipse\Cms\Admin\Filament\Resources\BannerResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\BannerResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBanner extends ViewRecord
{
    use ViewRecord\Concerns\Translatable;

    protected static string $resource = BannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
