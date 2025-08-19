<?php

namespace Eclipse\Cms\Admin\Filament\Resources\MenuItemResource\Pages;

use Eclipse\Cms\Admin\Filament\Resources\MenuItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMenuItem extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = MenuItemResource::class;
}
