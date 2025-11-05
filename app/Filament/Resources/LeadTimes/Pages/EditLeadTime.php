<?php

namespace App\Filament\Resources\LeadTimes\Pages;

use App\Filament\Resources\LeadTimes\LeadTimeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLeadTime extends EditRecord
{
    protected static string $resource = LeadTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
