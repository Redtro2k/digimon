<?php

namespace App\Classes;

use App\Enums\ReminderAttempt;
use App\Filament\Resources\Customers\Schemas\CustomerForm;
use App\Filament\Resources\Services\Schemas\ServiceForm;
use App\Filament\Resources\Vehicles\Schemas\VehicleForm;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;

class ServiceSection
{
    public static function customer(): Section
    {
        return Section::make('Customer Information')
            ->headerActions([
                Action::make('customer-edit')
                    ->size('sm')
                    ->modalIcon(LucideIcon::PenSquare)
                    ->modalDescription('Update the Customer Information')
                    ->icon(LucideIcon::PenSquare)
                    ->schema(fn(Schema $schema) => CustomerForm::configure($schema))
                    ->fillForm(fn($record) => $record->customer->toArray())
                    ->action(function($record, $data) {
                        $record->customer()->update($data);

                        return Notification::make()
                            ->title('Saved successfully')
                            ->success()
                            ->send();
                    })
            ])
            ->icon(LucideIcon::User)
            ->iconColor('primary')
            ->iconSize('lg')
            ->description('Basic details about the customer, including name, contact number, and address.')
            ->columns(3)
            ->schema([
                TextEntry::make('vehicle.customer.customer_name')
                    ->color('primary')
                    ->default('N/A')
                    ->label('Full Name'),
                TextEntry::make('vehicle.customer.provider')
                    ->label('Provider')
                    ->badge(),
                TextEntry::make('vehicle.customer.mobile_number')
                    ->color('primary')
                    ->label('Phone Number'),
                TextEntry::make('vehicle.customer.address')
                    ->columnSpanFull()
                    ->color('primary')
                    ->default('N/A')
                    ->label('Address'),
            ]);
    } // customer information

    public static function vehicle(): Section
    {
        return Section::make('Vehicle Information')
            ->headerActions([
                Action::make('vehicle-edit')
                    ->size('sm')
                    ->modalIcon(LucideIcon::PenSquare)
                    ->modalDescription('Update the Vehicle Information')
                    ->icon(LucideIcon::PenSquare)
                    ->schema(fn(Schema $schema) => VehicleForm::configure($schema))
                    ->fillForm(fn($record) => $record->vehicle->toArray())
                    ->action(function($record, $data) {
                        $record->vehicle()->update($data);

                        return Notification::make()
                            ->title('Saved successfully')
                            ->success()
                            ->send();
                    })
            ])
            ->icon(LucideIcon::Car)
            ->iconColor('primary')
            ->description('Key details about the vehicle.')
            ->columns()
            ->schema([
                TextEntry::make('vehicle.model')
                    ->label('Model')
                    ->default('N/A')
                    ->color('primary'),
                TextEntry::make('vehicle.cs_number')
                    ->label('CS Number')
                    ->default('N/A')
                    ->color('primary'),
                TextEntry::make('vehicle.plate')
                    ->label('Plate Number')
                    ->default('N/A')
                    ->color('primary'),
                TextEntry::make('vehicle.last_service_availed')
                    ->label('Last Service Availed')
                    ->default('N/A')
                    ->color('primary')
            ]);
    } // vehicle information

    public static function service(): Section
    {
        return Section::make('Service Information')
            ->description('Essential details regarding the service performed or availed.')
            ->headerActions([
                Action::make('service-edit')
                    ->size('sm')
                    ->modalIcon(LucideIcon::PenSquare)
                    ->modalDescription('Update the Service Information')
                    ->icon(LucideIcon::PenSquare)
                    ->schema(fn(Schema $schema) => ServiceForm::configure($schema))
                    ->fillForm(fn($record) => $record->toArray())
                    ->action(function($record, $data) {
                        $record->update($data);

                        return Notification::make()
                            ->title('Saved successfully')
                            ->success()
                            ->send();
                    })
            ])
            ->icon(LucideIcon::Wrench)
            ->iconColor('primary')
            ->columns(3)
            ->schema([
                TextEntry::make('recommended_pm_service')
                    ->label('Recommended PM Service')
                    ->default('N/A')
                    ->color('primary'),
                TextEntry::make('last_service_availed')
                    ->label('Last Service Availed')
                    ->default('N/A')
                    ->color('primary'),
                TextEntry::make('forecast_status')
                    ->label('Forecast Status')
                    ->default('N/A')
                    ->color(fn($state) => match($state) {
                        'open' => Color::Green,
                        'close' => Color::Red,
                    })
                    ->badge()
                    ->color('primary'),
                Fieldset::make('Personal')
                    ->schema([
                        TextEntry::make('personal_email')
                            ->columnSpanFull()
                            ->label('Personal Email')
                            ->default('N/A')
                            ->color('primary'),
                        TextEntry::make('personal_mobile')
                            ->columnSpanFull()
                            ->label('Personal Mobile')
                            ->default('N/A')
                            ->formatStateUsing(fn($state, $record) => MobileNumber::make($state)->formatted())
                            ->color('primary'),
                        TextEntry::make('personal_mobile')
                            ->columnSpanFull()
                            ->label('Provider')
                            ->default('N/A')
                            ->formatStateUsing(fn($state, $record) => MobileNumber::make($state)->provider())
                            ->color('primary'),
                    ]),
                Fieldset::make('Company')
                    ->schema([
                        TextEntry::make('company_email_address')
                            ->columnSpanFull()
                            ->label('Company Email')
                            ->default('N/A')
                            ->color('primary'),
                        TextEntry::make('company_mobile')
                            ->columnSpanFull()
                            ->label('Company Mobile')
                            ->default('N/A')
                            ->color('primary'),
                    ]),
                TextEntry::make('forecast_date')
                    ->label('Forecast/Appointment Date')
                    ->color('primary')
                    ->date()
            ]);
    } //service information

    public static function reminder(): Section
    {
        return Section::make('Call Attempts')
            ->icon(LucideIcon::Megaphone)
            ->headerActions([
                Action::make('New Attempt')
                ->size('sm')
                ->icon(LucideIcon::PhoneCall)
                ->color(Color::Blue)
                ->modalIcon(LucideIcon::PhoneCall)
                ->slideOver()
                ->schema([
                    TextInput::make('attempt')
                ]),
            ])
            ->iconColor('primary')
            ->description('Customer result of call attempts. max of 3 times')
            ->schema([
                RepeatableEntry::make('reminders')
                    ->columns()
                    ->schema([
                        TextEntry::make('attempt')
                            ->columnSpanFull()
                            ->hiddenLabel()
                            ->alignCenter()
                            ->size('lg')
                            ->color(fn($state) => $state->getColor())
                            ->weight(FontWeight::Bold)
                            ->formatStateUsing(fn($state): string => $state->getLabel()),
                        TextEntry::make('category_title')
                            ->label('Category'),
                        TextEntry::make('sub_result')
                            ->badge(),
                        TextEntry::make('call_back')
                            ->label('Call back Date')
                            ->dateTime()
                            ->placeholder('N/A'),
                        TextEntry::make('created_at')
                            ->label('Date Called')
                            ->dateTime()
                            ->since()
                    ])
            ]);
    }
}
