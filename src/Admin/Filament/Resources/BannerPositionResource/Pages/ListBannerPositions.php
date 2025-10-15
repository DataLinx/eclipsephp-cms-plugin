<?php

namespace Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;

class ListBannerPositions extends ListRecords
{
    use Translatable;

    protected static string $resource = BannerPositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus-circle')
                ->label('New Position'),
        ];
    }
}
