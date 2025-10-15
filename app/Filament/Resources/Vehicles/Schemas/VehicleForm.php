<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'customer_name')
                    ->required(),
                TextInput::make('cs_number')
                    ->required(),
                TextInput::make('plate')
                    ->required(),
                TextInput::make('model')
                    ->required(),
            ]);
    }
}
