<?php

namespace Eclipse\Cms\Filament\Resources\SectionResource\Pages;

use Eclipse\Cms\Filament\Resources\SectionResource;
use Eclipse\Common\Foundation\Pages\HasScoutSearch;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSections extends ListRecords
{
    use HasScoutSearch;

    protected static string $resource = SectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
