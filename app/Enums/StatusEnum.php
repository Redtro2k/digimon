<?php

namespace App\Enums;

use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Support\Colors\Color;

enum StatusEnum: string
{
    //
    case SUCCESSFUL = 'successful';
    case UNSUCCESSFUL = 'unsuccessful';

    public function getLabel(): string
    {
        return match($this){
            self::SUCCESSFUL => 'Successful',
            self::UNSUCCESSFUL => 'Unsuccessful',
        };
    }
    public function getColor(): array
    {
        return match($this){
            self::SUCCESSFUL => Color::Green,
            self::UNSUCCESSFUL => Color::Red,
        };
    }
    public function getIcon(): LucideIcon
    {
        return match($this){
            self::SUCCESSFUL => LucideIcon::Check,
            self::UNSUCCESSFUL => LucideIcon::X,
        };
    }
}
