<?php

namespace App\Classes;

use App\Filament\Resources\Customers\Schemas\CustomerForm;
use App\Filament\Resources\Services\Schemas\ServiceForm;
use App\Filament\Resources\Vehicles\Schemas\VehicleForm;
use App\Models\Category;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\EmptyState;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
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
                    ->color('secondary')
                    ->default('N/A')
                    ->label('Full Name'),
                TextEntry::make('vehicle.customer.provider')
                    ->label('Provider')
                    ->badge(),
                TextEntry::make('vehicle.customer.mobile_number')
                    ->color('secondary')
                    ->formatStateUsing(fn($state, $record) => MobileNumber::make($state)->formatted())
                    ->label('Phone Number'),
                TextEntry::make('vehicle.customer.address')
                    ->columnSpanFull()
                    ->color('secondary')
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
                    ->color('secondary'),
                TextEntry::make('vehicle.cs_number')
                    ->label('CS Number')
                    ->default('N/A')
                    ->color('secondary'),
                TextEntry::make('vehicle.plate')
                    ->label('Plate Number')
                    ->default('N/A')
                    ->color('secondary'),
                TextEntry::make('vehicle.last_service_availed')
                    ->label('Last Service Availed')
                    ->default('N/A')
                    ->color('secondary')
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
                    ->color('secondary'),
                TextEntry::make('last_service_availed')
                    ->label('Last Service Availed')
                    ->default('N/A')
                    ->color('secondary'),
                TextEntry::make('forecast_status')
                    ->label('Forecast Status')
                    ->default('N/A')
                    ->color(fn($state) => match($state) {
                        'open' => Color::Green,
                        'close' => Color::Red,
                    })
                    ->badge()
                    ->color('secondary'),
                Fieldset::make('Personal')
                    ->schema([
                        TextEntry::make('personal_email')
                            ->columnSpanFull()
                            ->label('Personal Email')
                            ->default('N/A')
                            ->color('secondary'),
                        TextEntry::make('personal_mobile')
                            ->columnSpanFull()
                            ->label('Personal Mobile')
                            ->default('N/A')
                            ->formatStateUsing(fn($state, $record) => MobileNumber::make($state)->formatted())
                            ->color('secondary'),
                        TextEntry::make('personal_mobile')
                            ->columnSpanFull()
                            ->label('Provider')
                            ->default('N/A')
                            ->formatStateUsing(fn($state, $record) => MobileNumber::make($state)->provider())
                            ->color('secondary'),
                    ]),
                Fieldset::make('Company')
                    ->schema([
                        TextEntry::make('company_email_address')
                            ->columnSpanFull()
                            ->label('Company Email')
                            ->default('N/A')
                            ->color('secondary'),
                        TextEntry::make('company_mobile')
                            ->columnSpanFull()
                            ->label('Company Mobile')
                            ->default('N/A')
                            ->color('secondary'),
                    ]),
                TextEntry::make('forecast_date')
                    ->label('Forecast/Appointment Date')
                    ->color('secondary')
                    ->date()
            ]);
    } //service information

    public static function reminder(): Section
    {

        return Section::make('Call Attempts')
            ->icon(LucideIcon::Megaphone)
            ->headerActions([self::createAttempt()])
            ->iconColor('primary')
            ->description('Customer result of call attempts. max of 3 times')
            ->schema([
                EmptyState::make('No Called Attempt')
                    ->description('This record shows a No Called Attempt status, indicating that the agent did not initiate a call.')
                    ->icon(LucideIcon::PhoneCall)
                    ->footer([
                        self::createAttempt()
                    ])
                    ->visible(fn($record) => !$record->latestReminder()->exists()),

                    Grid::make()
                        ->columns(4)
                        ->visible(fn($record) => $record->latestReminder()->exists())
                        ->schema([
                            TextEntry::make('latestReminder.attempt')
                                ->columnSpanFull()
                                ->label('Latest Reminder')
                                ->size('lg')
                                ->color(fn($state) => $state->getColor())
                                ->weight(FontWeight::Bold)
                                ->formatStateUsing(fn($state): string => $state->getLabel()),
                            TextEntry::make('latestReminder.category_title')
                                ->columnSpan(2)
                                ->color('secondary')
                                ->label('Category'),
                            TextEntry::make('latestReminder.sub_result')
                                ->formatStateUsing(fn($state, $record) => $state->getLabel())
                                ->icon(fn($state) => $state->getIcon())
                                ->color(fn($state) => $state->getColor())
                                ->badge(),
                            TextEntry::make('latestReminder.call_back')
                                ->label('Call back Date')
                                ->color('secondary')
                                ->dateTime('M d, Y h:i A')
                                ->placeholder('N/A'),
                            TextEntry::make('latestReminder.created_at')
                                ->label('Date Called')
                                ->color('secondary')
                                ->size('xs')
                                ->dateTime()
                                ->since(),
                        ])
            ])
            ->footerActionsAlignment(Alignment::Center)
            ->footerActions([
                Action::make('View All Attempts')
                ->visible(fn($record) => $record->latestReminder()->exists())
                ->link()
                ->icon(LucideIcon::Eye)
                ->modalIcon(LucideIcon::Megaphone)
                ->modalDescription('the result of all called attempts.')
                ->schema([
                    RepeatableEntry::make('reminders')
                        ->hiddenLabel()
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
                                ->color(fn($state) => $state->getColor())
                                ->formatStateUsing(fn($state): string => $state->getLabel())
                                ->icon(fn($state) => $state->getIcon())
                                ->badge(),
                            TextEntry::make('call_back')
                                ->label('Call back Date')
                                ->dateTime('M d, Y h:i A')
                                ->placeholder('N/A'),
                            TextEntry::make('created_at')
                                ->label('Date Called')
                                ->since()
                        ])
                        ->grid(3)
                ])
                ->modalSubmitAction(false)
                ->modalFooterActionsAlignment(Alignment::Right)
            ]);
    }

    public static function createAttempt(): Action
    {
        return Action::make('New Attempt')
            ->disabled(fn($record) => $record->latestReminder->attempt->value == 3 || $record->has_completed)
            ->size('sm')
            ->icon(LucideIcon::PhoneCall)
            ->modalIcon(LucideIcon::PhoneCall)
            ->modalDescription('Indicates that the agent has made a new attempt to reach the customer.')
            ->schema([
                Grid::make()
                    ->columns(3)
                    ->schema([
                        TextInput::make('attempt')
                            ->default(fn($record) => $record->reminders()->max('attempt') + 1 ?? 1)
                            ->disabled()
                            ->hidden()
                            ->dehydrated(),
                        Select::make('sub_result')
                            ->label('Call Result')
                            ->native(false)
                            ->required(fn (Get $get): bool => !empty($get('category_id')))
                            ->options(fn() => Category::query()
                                ->where('what_field', 'reminder_category')
                                ->pluck('status', 'status')
                                ->toArray())
                            ->live(),
                        Select::make('category_id')
                            ->label('Sub Result')
                            ->native(false)
                            ->required(fn(Get $get): bool => !empty($get('sub_result')))
                            ->options(fn(Get $get) => Category::query()
                                ->where('what_field', 'reminder_category')
                                ->where('status', $get('sub_result'))
                                ->pluck('name', 'id')
                                ->toArray()),
                        DateTimePicker::make('call_back')
                            ->label('Call Back Date')
                            ->timezone(config('app.timezone'))
                            ->native(false)
                    ])
            ])
            ->action(function(array $data, $record) {
                $attempt = ($record->latestReminder?->attempt?->value ?? 0) + 1;

                $record->reminders()->create([
                    ...$data,
                    'assigned_to' => auth()->id(),
                    'attempt' => $attempt,
                ]);

                Notification::make()
                    ->title('Call finish Successfully')
                    ->icon(LucideIcon::Check)
                    ->body("You have finish completed the call with {$record->customer_name}. A follow-up reminder has been scheduled.")
                    ->success()
                    ->send();
            });
    }
}
