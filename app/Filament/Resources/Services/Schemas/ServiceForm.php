<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('vehicle_id')
                    ->required()
                    ->numeric(),
                TextInput::make('recommended_pm_service'),
                TextInput::make('forecast_status'),
                DatePicker::make('forecast_date')
                    ->required(),
                TextInput::make('personal_email')
                    ->email(),
                TextInput::make('personal_mobile'),
                TextInput::make('company_email_address')
                    ->email(),
                TextInput::make('company_mobile'),
                Toggle::make('has_fpm')
                    ->required(),
            ]);
    }
}
