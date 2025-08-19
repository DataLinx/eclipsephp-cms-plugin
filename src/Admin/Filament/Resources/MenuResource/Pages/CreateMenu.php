<?php

namespace Eclipse\Cms\Admin\Filament\Resources\MenuResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\MenuResource;
use Filament\Actions;
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
            $tenantModel = config('eclipse-cms.tenancy.model');
            if ($tenantModel && class_exists($tenantModel)) {
                $tenantFK = config('eclipse-cms.tenancy.foreign_key', 'site_id');
                $data[$tenantFK] = $tenantModel::getCurrent()?->id;
            }
        }

        return $data;
    }
}
