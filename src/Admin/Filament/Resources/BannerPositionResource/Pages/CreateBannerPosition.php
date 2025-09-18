<?php

namespace Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\BannerPositionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBannerPosition extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = BannerPositionResource::class;

    protected static ?string $breadcrumb = 'Create Position';

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('view');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
