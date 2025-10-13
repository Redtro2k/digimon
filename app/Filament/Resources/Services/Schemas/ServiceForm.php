<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Filament\Resources\Services\Pages\EditService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Select::make('vehicle_id')
                    ->disabledOn('edit')
                    ->relationship('vehicle', 'model')
                    ->required(),
                TextInput::make('recommended_pm_service'),
                TextInput::make('last_service_availed'),
                Radio::make('forecast_status')
                    ->inline()
                    ->options([
                        'open' => 'Open',
                        'closed' => 'Closed',
                    ]),
                DatePicker::make('forecast_date')
                    ->native(false)
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
