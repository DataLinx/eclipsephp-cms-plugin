<?php

namespace Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBannerPositions extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = BannerPositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Position'),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
