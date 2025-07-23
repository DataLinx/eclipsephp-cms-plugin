<?php

namespace Eclipse\Cms\Enums;

use Filament\Support\Contracts\HasLabel;

enum SectionType implements HasLabel
{
    case Pages;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pages => 'Pages',
        };
    }
}
