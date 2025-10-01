<?php

namespace Eclipse\Cms\Enums;

use Filament\Support\Contracts\HasLabel;

enum MenuItemType implements HasLabel
{
    case Linkable;
    case CustomUrl;
    case Group;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Linkable => 'Data record',
            self::CustomUrl => 'Custom URL',
            self::Group => 'Group',
        };
    }
}
