<?php

namespace Eclipse\Cms\Enums;

use Filament\Support\Contracts\HasLabel;

enum SectionType: string implements HasLabel
{
    case Pages = 'pages';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pages => 'Pages',
        };
    }
}
