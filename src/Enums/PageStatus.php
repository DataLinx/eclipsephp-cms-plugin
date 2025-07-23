<?php

namespace Eclipse\Cms\Enums;

use Filament\Support\Contracts\HasLabel;

enum PageStatus implements HasLabel
{
    case Draft;
    case Published;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
        };
    }
}
