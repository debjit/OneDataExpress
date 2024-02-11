<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PostStatus: int implements HasColor, HasIcon, HasLabel
{
    case DRAFT = 0; //When Created with only html
    case PROCESSED = 1; //When converted from html to markdown
    case ERROR = 2; //Any error code
    case PUBLISHED = 3; //Successfully published

    public const DEFAULT = self::DRAFT->value;

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::DRAFT => 'heroicon-o-pencil-square',
            self::PROCESSED => 'heroicon-o-forward',
            self::ERROR => 'heroicon-o-exclamation-triangle',
            self::PUBLISHED => 'heroicon-o-shield-check',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::DRAFT => Color::Green,
            self::PROCESSED => Color::Blue,
            self::ERROR => Color::Red,
            self::PUBLISHED => Color::Orange,
        };
    }
}
