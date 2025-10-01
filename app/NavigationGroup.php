<?php

namespace App;

use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum NavigationGroup implements HasLabel, HasIcon
{
    //
    case MIS;
    case Manager;
    case ServiceAdmin;
    case MRAS;

    public function getLabel(): string
    {
        return match($this) {
            self::MIS => 'Account Management',
            self::Manager => 'Lead Time & Reports',
            self::ServiceAdmin => 'Uploading & Assigning',
            self::MRAS => 'Forecast Management',
        };
    }

    public function getIcon(): LucideIcon
    {
        return match($this) {
            self::MIS => LucideIcon::Users,
            self::Manager => LucideIcon::Table,
            self::ServiceAdmin => LucideIcon::ArrowDownFromLine,
            self::MRAS => LucideIcon::Phone,
        };
    }
}
