<?php

namespace Eclipse\Cms\Admin\Filament\Resources\MenuResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\MenuResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;

class CreateMenu extends CreateRecord
{
    use Translatable;

    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (config('eclipse-cms.tenancy.enabled')) {
            $tenantFK = config('eclipse-cms.tenancy.foreign_key', 'site_id');
            $data[$tenantFK] = Filament::getTenant()->id;
        }

        return $data;
    }
}
