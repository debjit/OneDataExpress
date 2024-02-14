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
    public function getIcon(): ?string
    {
        return match ($this) {
            self::DRAFT => 'heroicon-o-pencil-square',
            self::PUBLISHED => 'heroicon-o-shield-check',
            self::ERROR => 'heroicon-o-exclamation-triangle',
        };
    }
}
