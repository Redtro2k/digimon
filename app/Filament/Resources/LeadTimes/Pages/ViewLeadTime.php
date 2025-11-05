<?php

namespace App\Filament\Resources\LeadTimes\Pages;

use App\Filament\Resources\LeadTimes\LeadTimeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLeadTime extends ViewRecord
{
    protected static string $resource = LeadTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
