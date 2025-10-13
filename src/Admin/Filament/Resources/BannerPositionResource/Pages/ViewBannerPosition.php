<?php

namespace Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\SpatieTranslatable\Resources\Pages\ViewRecord\Concerns\Translatable;

class ViewBannerPosition extends ViewRecord
{
    use Translatable;

    protected static ?string $breadcrumb = 'Manage Banners';

    protected static string $resource = BannerPositionResource::class;

    public function getTitle(): string
    {
        return $this->getRecord()->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('heroicon-o-pencil-square')
                ->label('Edit Position'),
            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash')
                ->label('Delete position'),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            BannerPositionResource\RelationManagers\BannerRelationManager::class,
        ];
    }
}
