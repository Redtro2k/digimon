<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Resources\Services\ServiceResource;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Colors\Color;

class ViewService extends ViewRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_as_completed')
                ->hidden(fn($record) => $record->has_completed)
                ->color(Color::Teal)
                ->icon(LucideIcon::CircleCheckBig)
                ->requiresConfirmation()
                ->successNotification(fn($record) => Notification::make()
                        ->title('Service Completed')
                        ->body("{$record->customer->customer_name} has been marked as completed successfully.")
                        ->icon(LucideIcon::CheckCheck)
                        ->iconColor('success')
                        ->success())
                ->action(function($record){
                     $record->update(['has_completed' => true]);
                })
        ];
    }
}
