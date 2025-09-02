<?php

namespace Eclipse\Cms\Admin\Filament\Resources\BannerResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource;
use Eclipse\Cms\Admin\Filament\Resources\BannerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBanners extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = BannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus-circle')
                ->label('Banner'),
            Actions\Action::make('positions')
                ->icon('heroicon-o-list-bullet')
                ->color('warning')
                ->url(
                    BannerPositionResource::getUrl('index')
                ),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
