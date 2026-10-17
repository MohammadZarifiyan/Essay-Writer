<?php

namespace App\Enums;

enum EssayLength: string
{
    case Short = 'short';
    case Medium = 'medium';
    case Long = 'long';
    case VeryLong = 'very_long';

    public function getWordCount(): int
    {
        return match ($this) {
            self::Short => 150,
            self::Medium => 250,
            self::Long => 400,
            self::VeryLong => 600,
        };
    }
}
