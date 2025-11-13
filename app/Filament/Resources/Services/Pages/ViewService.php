<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Resources\Services\ServiceResource;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;


class ViewService extends ViewRecord
{
    protected static string $resource = ServiceResource::class;

    protected $listeners = [
        'refreshResourceForm' => '$refresh',
    ];

    protected function getHeaderActions(): array
    {
        return [
//            Action::make('mark_as_completed')
//                ->disabled(fn($record) => $record->has_completed || ($record->has_customer_attend === null && $record->latestReminder != 'successful'))
//                ->hidden(fn($record) => !$record->reminders()->exists())
//                ->color(Color::Teal)
//                ->icon(LucideIcon::CircleCheckBig)
//                ->requiresConfirmation()
//                ->successNotification(fn($record) => Notification::make()
//                        ->title('Service Completed')
//                        ->body("{$record->customer->customer_name} has been marked as completed successfully.")
//                        ->icon(LucideIcon::CheckCheck)
//                        ->iconColor('success')
//                        ->success())
//                ->action(function($record){
//                     $record->update(['has_completed' => true]);
//                }),
            Action::make('has_customer_attend')
                ->disabled(fn($record) => $record->has_completed)
                ->hidden(fn($record) => !$record->reminders()->exists() || !auth()->user()->hasRole('mras'))
                ->requiresConfirmation()
                ->icon(LucideIcon::Check)
                ->label('Mark as Arrived')
                ->modelLabel('Has Customer Arrived?')
                ->icon(LucideIcon::UserCheck)
                ->modal()
                ->modalIcon(LucideIcon::UserCheck)
                ->modalHeading('Confirm Customer Arrival')
                ->modalDescription('Used to track if the customer has physically arrived or participated in their appointment or call-back on the set date.')
                ->schema([
                    Radio::make('customer_attend')
                        ->label('Has Customer Arrived?')
                        ->helperText('check if the status of customer has arrived or participated in their appointment or call-back.')
                        ->options([
                            'yes' => 'Yes, Make has Arrived',
                            'no' => 'No, Mark Customer Not Arrived',
                            'other' => 'Other'
                        ])
                        ->descriptions([
                            'no' => "Update the latest call attempt to 'Customer Not Around'.",
                            'other' => "if the customer has too early or late."
                        ])
                        ->required()
                        ->reactive(),
                    Radio::make('repoll')
                        ->label('Should this customer be returned to call polling?')
                        ->options([
                            true => 'Yes',
                            false => 'No'
                        ])
                        ->default(true)
                        ->requiredIf('customer_attend', 'no')
                        ->visible(fn(Get $get) => $get('customer_attend') == 'no'),
                    Fieldset::make('Update Latest Call Attempts')
                        ->schema([
                            Select::make('category_id')
                                ->label('Sub Result')
                                ->native(false)
                                ->columnSpanFull()
                                ->options([
                                    2 => 'Too Early',
                                    3 => 'Too Late',
                                ])
                                ->required()
                        ])
                        ->visible(fn(Get $get) => $get('customer_attend') == 'other')
                ])
                ->action(function ($record, $data): void {
                    if ($data['customer_attend'] === 'yes') {
                        $record->update(['has_completed' => true]);
                    }
                    elseif ($data['customer_attend'] === 'no') {
                        $record->latestReminder()->update([
                            'category_id' => 23,
                            'sub_result' => 'unsuccessful',
                        ]);
                        if(!$data['repoll']){
                            $record->update('has_completed', true);
                        }
                    } elseif ($data['customer_attend'] === 'other') {
                        $record->update(['has_completed' => true]);
                        $record->latestReminder()->update(['category_id' => $data['category_id']]);
                    }
                })
        ];
    }
}
