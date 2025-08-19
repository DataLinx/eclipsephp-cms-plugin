<?php

namespace Eclipse\Cms\Admin\Filament\Resources\MenuItemResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\MenuItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMenuItems extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = MenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\CreateAction::make()
                ->label('Create')
                ->icon('heroicon-o-plus-circle'),
            Actions\Action::make('sorting')
                ->icon('heroicon-o-arrows-up-down')
                ->color('gray')
                ->url(fn () => self::$resource::getUrl('sorting')),
        ];
    }
}
