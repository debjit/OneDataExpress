<?php

namespace App\Enums;

enum PostStatus: int
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
}
