<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

enum Gender: string
{
    //
    case MALE = 'male';
    case FEMALE = 'female';
    case OTHER = 'other';

    public function getLabel(): string
    {
        return match ($this){
           self::MALE => 'Male',
            self::FEMALE => 'Female',
            self::OTHER => 'Other',
        };
    }

    public function avatar(): string
    {
        return match ($this) {
            self::MALE => 'https://avatar.iran.liara.run/public/boy?username=',
            self::FEMALE => 'https://avatar.iran.liara.run/public/girl?username=',
            self::OTHER => 'https://avatar.iran.liara.run/username?username=' ,
        };
    }

    public function title(): string
    {
        return match ($this) {
            self::MALE => 'Mr.',
            self::FEMALE => 'Ms.',
            self::OTHER => '',
        };
    }
}
