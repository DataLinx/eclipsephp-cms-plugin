<?php

namespace Eclipse\Cms\Admin\Filament\Resources\PageResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\PageResource;
use Eclipse\Common\Foundation\Pages\HasScoutSearch;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPages extends ListRecords
{
    use HasScoutSearch;

    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
