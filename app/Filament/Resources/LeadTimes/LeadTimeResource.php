<?php

namespace App\Filament\Resources\LeadTimes;

use App\Filament\Resources\LeadTimes\Pages\CreateLeadTime;
use App\Filament\Resources\LeadTimes\Pages\EditLeadTime;
use App\Filament\Resources\LeadTimes\Pages\ListLeadTimes;
use App\Filament\Resources\LeadTimes\Pages\ViewLeadTime;
use App\Filament\Resources\LeadTimes\Schemas\LeadTimeForm;
use App\Filament\Resources\LeadTimes\Schemas\LeadTimeInfolist;
use App\Filament\Resources\LeadTimes\Tables\LeadTimesTable;
use BackedEnum;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

class LeadTimeResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::History;

    public static function form(Schema $schema): Schema
    {
        return LeadTimeForm::configure($schema);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('viewAny', Activity::class);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LeadTimeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadTimesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeadTimes::route('/'),
//            'create' => CreateLeadTime::route('/create'),
//            'view' => ViewLeadTime::route('/{record}'),
//            'edit' => EditLeadTime::route('/{record}/edit'),
        ];
    }
}
