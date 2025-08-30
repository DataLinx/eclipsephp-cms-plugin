<?php

namespace Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBannerPosition extends ViewRecord
{
    use ViewRecord\Concerns\Translatable;

    protected static string $resource = BannerPositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
