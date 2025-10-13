<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('source')
                    ->required(),
                TextInput::make('customer_name')
                    ->required(),
                TextInput::make('provider')
                    ->required(),
                TextInput::make('mobile_number'),
                TextInput::make('address'),
            ]);
    }
}
