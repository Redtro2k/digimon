<?php

namespace App\Filament\Resources\Dealers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class DealerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))) // slug
                    ->afterStateUpdated(fn(Set $set, ?string $state) => $set('acronym', Str::acronym($state)))
                    ->required(),
                TextInput::make('acronym')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
            ]);
    }
}
