<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Resources\Vehicles\VehicleResource;
use App\Filament\Widgets\CustomerTimerWidget;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;

class CallService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    public function getTitle(): string
    {
        return 'Call for '. $this->record->customer->customer_name;
    }
    public function getBreadcrumb(): string
    {
        return 'Call for '. $this->record->customer->customer_name;
    }

    public function getHeaderWidgets(): array
    {
        return [
            CustomerTimerWidget::make([
                'recordId' => $this->record->id,
            ])
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            Action::make('edit_vehicle')
                    ->color(Color::Indigo)
                    ->icon(LucideIcon::PenSquare)
                    ->action(fn($record, $livewire) => $livewire->redirect(VehicleResource::getUrl('edit', ['record' => $record->vehicle->id]))),
            Action::make('edit_customer')
                ->color(Color::Indigo)
                ->icon(LucideIcon::PenSquare)
                ->action(fn($record, $livewire) => $livewire->redirect(CustomerResource::getUrl('edit', ['record' => $record->customer->id])))
        ];
    }
}
