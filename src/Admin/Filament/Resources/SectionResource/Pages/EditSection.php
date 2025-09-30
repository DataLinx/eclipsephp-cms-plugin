<?php

namespace Eclipse\Cms\Admin\Filament\Resources\SectionResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\SectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSection extends EditRecord
{
    use EditRecord\Concerns\Translatable;

    protected static string $resource = SectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
