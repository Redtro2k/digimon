<?php

namespace App\Enums;

use App\Models\User;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Support\Colors\Color;

enum LogActivities: string
{
    //
    case SIGN_IN = 'login';
    case SIGN_OUT = 'logout';

    public function  getType(): string
    {
        return match($this) {
            self::SIGN_IN => 'Login',
            self::SIGN_OUT => 'Logout',
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::SIGN_IN => auth()->user()->name.' have been logged.',
            self::SIGN_OUT =>  auth()->user()->name.' You have been logout.',
        };
    }
    public function getIcon(): LucideIcon
    {
        return match($this) {
            self::SIGN_IN => LucideIcon::LogIn,
            self::SIGN_OUT => LucideIcon::LogOut,
        };
    }

    public function getColor(): array
    {
        return match($this) {
            self::SIGN_IN => Color::Green,
            self::SIGN_OUT => Color::Red,
        };
    }
}
