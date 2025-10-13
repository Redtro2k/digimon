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
            ->components([
                Select::make('vehicle_id')
                    ->disabled()
                    ->relationship('vehicle', 'model')
                    ->required(),
                TextInput::make('recommended_pm_service'),
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
                Wizard::make([
                    Step::make('Attempt 1')
                        ->schema([
                            TextInput::make('assigned_to')
                                ->afterStateHydrated(function (TextInput $component, $state) {
                                    if (blank($state)) {
                                        $component->state(auth()->id());
                                    }
                                }),
                            TextInput::make('attempt')
                                ->afterStateHydrated(function (TextInput $component, $state) {
                                    if (blank($state)) {
                                        $component->state(1);
                                    }
                                }),
                        ])
                ])
                ->columnSpanFull()
                ->hiddenOn(EditService::class)
            ]);
    }
}
