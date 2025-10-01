<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\Register as BaseRegistration;
use Filament\Forms\Components\TextInput;
use \Filament\Schemas\Schema;

class NewRegistration extends BaseRegistration
{
    public function form(Schema $schema): Schema
    {
        return $schema
                ->schema([
                    TextInput::make('username')
                    ->required()
                    ->unique('users', 'username')
                    ->maxLength(255),
                    $this->getNameFormComponent(),
                    $this->getEmailFormComponent(),
                    $this->getPasswordFormComponent(),
                    $this->getPasswordConfirmationFormComponent(),
                ]);

    }
}
