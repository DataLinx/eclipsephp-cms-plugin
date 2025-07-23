<?php

namespace Eclipse\Cms\Admin\Filament\Resources\PageResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\PageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPages extends ListRecords
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
