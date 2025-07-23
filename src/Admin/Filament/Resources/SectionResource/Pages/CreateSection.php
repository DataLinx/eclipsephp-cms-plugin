<?php

namespace Eclipse\Cms\Admin\Filament\Resources\SectionResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\SectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSection extends CreateRecord
{
    protected static string $resource = SectionResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
