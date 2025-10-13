<?php

namespace Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\EditRecord\Concerns\Translatable;

class EditBannerPosition extends EditRecord
{
    use Translatable;

    protected static ?string $breadcrumb = 'Edit Position';

    protected static string $resource = BannerPositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            Actions\ViewAction::make()
                ->color('primary')
                ->icon('heroicon-o-photo')
                ->label('Manage Banners'),
            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash')
                ->label('Delete Position'),
        ];
    }
}
