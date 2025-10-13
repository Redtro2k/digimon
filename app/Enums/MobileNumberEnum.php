<?php

namespace App\Enums;

use Filament\Support\Facades\FilamentColor;
use Filament\Support\Colors\Color;

enum MobileNumberEnum: string
{
    case SMART = 'Smart';
    case TNT = 'TNT';
    case SUN = 'Sun';
    case GLOBE = 'Globe';
    case GLOBE_TM = 'Globe or TM';
    case CHERRY = 'Cherry';
    case GOMO = 'GOMO';
    case DITO = 'DITO';

    public function badgeColor(): array
    {
        return match ($this) {
            self::SMART, self::TNT, self::SUN => Color::Green,
            self::GLOBE, self::GLOBE_TM => Color::Blue,
            self::DITO => Color::Red,
            self::CHERRY => Color::Yellow,
            self::GOMO => Color::Purple,
            default => Color::Gray
        };
    }
}
