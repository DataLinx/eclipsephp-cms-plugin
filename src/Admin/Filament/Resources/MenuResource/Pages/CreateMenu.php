<?php

namespace Eclipse\Cms\Admin\Filament\Resources\MenuResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\MenuResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateMenu extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
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
