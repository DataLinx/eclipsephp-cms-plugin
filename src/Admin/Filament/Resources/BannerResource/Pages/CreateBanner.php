<?php

namespace Eclipse\Cms\Admin\Filament\Resources\BannerResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\BannerResource;
use Eclipse\Cms\Models\Banner;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBanner extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = BannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['position_id'])) {
            $maxSort = Banner::where('position_id', $data['position_id'])->max('sort') ?? 0;

            $data['sort'] = $maxSort + 1;
        }

        return $data;
    }
}
