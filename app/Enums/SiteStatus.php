<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SiteStatus: int implements HasLabel
{
    case DRAFT = 0;
    case PUBLISHED = 1;
    case ERROR = 2;

    public const DEFAULT = self::DRAFT->value;

    public function getLabel(): ?string
    {
        return $this->name;
    }
}
