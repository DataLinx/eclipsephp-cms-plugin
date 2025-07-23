<?php

namespace Eclipse\Cms\Admin\Filament\Resources\PageResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\PageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
