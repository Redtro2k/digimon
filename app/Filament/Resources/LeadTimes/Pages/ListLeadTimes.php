<?php

namespace App\Filament\Resources\LeadTimes\Pages;

use App\Filament\Resources\LeadTimes\LeadTimeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLeadTimes extends ListRecords
{
    protected static string $resource = LeadTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
