<?php

namespace App\Enums;

use Filament\Support\Colors\Color;

enum ReminderAttempt: string
{
    //
    case FIRST = '1';
    case SECOND = '2';
    case THIRD = '3';

    public function getLabel(): string
    {
        return match ($this) {
            self::FIRST => '1st Attempt',
            self::SECOND => '2nd Attempt',
            self::THIRD => '3rd Attempt',
        };
    }
    public function getColor(): array
    {
        return match($this) {
            self::FIRST => Color::Green,
            self::SECOND => Color::Yellow,
            self::THIRD => Color::Red,
        };
    }
}
