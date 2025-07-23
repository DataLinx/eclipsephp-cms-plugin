<?php

namespace Eclipse\Cms\Admin\Filament\Resources\SectionResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\SectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSections extends ListRecords
{
    protected static string $resource = SectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
