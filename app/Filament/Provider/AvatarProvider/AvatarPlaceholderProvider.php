<?php

namespace App\Filament\Provider\AvatarProvider;

use Filament\AvatarProviders\Contracts;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class AvatarPlaceholderProvider implements Contracts\AvatarProvider
{
    public function get(Model | Authenticatable $record): string
    {
        $name = str(Filament::getNameForDefaultAvatar($record))
            ->headline()
            ->replace(' ', '+');



//        ucwords(Str::replace(' ', '+', $username))

        return $record->gender->avatar().$name;
    }
}
