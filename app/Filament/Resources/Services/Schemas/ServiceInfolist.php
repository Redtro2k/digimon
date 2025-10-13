<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Filament\Resources\Services\Pages\CallService;
use App\Filament\Resources\Services\ServiceResource;
use App\Models\User;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Infolists\Components\SpatieTagsEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Kirschbaum\Commentions\Filament\Actions\CommentsAction;

class ServiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                        Section::make('Customer Information')
                            ->icon(LucideIcon::User)
                            ->iconColor('primary')
                            ->iconSize('lg')
                            ->description('Basic details about the customer, including name, contact number, and address.')
                            ->columns()
                            ->schema([
                                TextEntry::make('vehicle.customer.customer_name')
                                    ->color('primary')
                                    ->default('N/A')
                                    ->label('Full Name'),
                                TextEntry::make('vehicle.customer.provider')
                                    ->label('Phone Number')
                                    ->badge()
                                    ->size('lg') // or 'xl', 'md', 'sm', 'xs'
                                    ->suffix(fn ($record) => ' • ' . ($record->vehicle?->customer?->mobile_number ?? 'N/A')),
                                TextEntry::make('vehicle.customer.address')
                                    ->columnSpanFull()
                                    ->color('primary')
                                    ->default('N/A')
                                    ->label('Address'),
                            ]),
                        Section::make('Vehicle Information')
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
                            ]),
                        Section::make('Service Information')
                            ->description('Essential details regarding the service performed or availed.')
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
                                            ->label('Personal Mobile #')
                                            ->default('N/A')
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
                                            ->label('Company Mobile #')
                                            ->default('N/A')
                                            ->color('primary'),
                                    ]),
                                TextEntry::make('forecast_date')
                                    ->label('Forecast/Appointment Date')
                                    ->color('primary')
                                    ->date()
                            ]),
                        Section::make('Quick Action')
                            ->headerActions([
                                    Action::make('call_this_customer')
                                        ->icon(LucideIcon::Phone)
                                        ->color('secondary')
                                        ->tooltip('call this customer')
                                        ->hiddenLabel()
                                        ->button()
                                        ->action(fn($record, $livewire) => $livewire->redirect(ServiceResource::getUrl('call', ['record' => $record->id]))),
                                    Action::make('add_tags')
                                        ->label('Tag')
                                        ->tooltip('add tag this customer')
                                        ->hiddenLabel()
                                        ->color('secondary')
                                        ->modal()
                                        ->modalWidth('sm')
                                        ->icon(LucideIcon::Tags)
                                        ->button()
                                        ->schema([
                                            SpatieTagsInput::make('tags')
                                                ->autofocus(false)
                                                ->type('categories')
                                        ]),
                                CommentsAction::make()
                                    ->disableSidebar()
                                    ->hiddenLabel()
                                    ->mentionables(User::all())
                            ])
                            ->icon(LucideIcon::Zap)
                            ->iconColor('primary')
                            ->description('Key details about the quick actions.')
                            ->columns(3)
                            ->schema([
                                    SpatieTagsEntry::make('tags')
                                        ->columnSpanFull(),
                            ])
            ]);
    }
}
