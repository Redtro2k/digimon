<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Gender;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('profile')
                    ->label('Profile image')
                    ->disk('public')
                    ->avatar()
                    ->circleCropper()
                    ->directory('user-images')
                    ->columnSpanFull(),
                TextInput::make('username')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                Radio::make('gender')
                    ->options(fn() => collect(Gender::cases())
                        ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])
                        ->all())
                    ->inline(),
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                TextInput::make('password')
                    ->revealable()
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn($state) => filled($state))
                    ->maxLength(255),
                Select::make('dealer_id')
                    ->multiple()
                    ->preload()
                    ->relationship('dealer', 'acronym'),
            ]);
    }
}
