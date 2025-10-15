<?php

namespace Eclipse\Cms\Enums;

use Filament\Support\Contracts\HasLabel;

enum MenuItemType: string implements HasLabel
{
    case Linkable = 'Linkable';
    case CustomUrl = 'CustomUrl';
    case Group = 'Group';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Linkable => 'Data record',
            self::CustomUrl => 'Custom URL',
            self::Group => 'Group',
        };
    }
}
