<?php

namespace App\Filament\Resources\Services;

use App\Filament\Resources\Services\Pages\CallService;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Filament\Resources\Services\Pages\ViewService;
use App\Filament\Resources\Services\Schemas\ServiceForm;
use App\Filament\Resources\Services\Schemas\ServiceInfolist;
use App\Filament\Resources\Services\Tables\ServicesTable;
use App\Models\Service;
use App\NavigationGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|null|\UnitEnum $navigationGroup = NavigationGroup::MRAS;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $recordTitleAttribute = 'title';
    protected static ?string $navigationLabel = 'List Forecast Service';
    protected static ?string $modelLabel = 'Forecast Service';
    protected static ?string $pluralModelLabel = 'Forecast Services';
    public static function getGloballySearchableAttributes(): array
   {
        return ['vehicle.plate', 'vehicle.cs_number','vehicle.customer.customer_name', 'vehicle.model'];
   }
    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return static::getUrl('view', ['record' => $record]);
    }
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        $model = $record->load('vehicle')->vehicle->model;
        $customer = $record->load('vehicle.customer')->vehicle->customer->customer_name;
        return "<div><h1>$model</h1></div><small>$customer</small>";
    }

    public static function form(Schema $schema): Schema
    {
        return ServiceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ServiceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServicesTable::configure($table);
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
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'view' => ViewService::route('/{record}'),
            'edit' => EditService::route('/{record}/edit'),
            'call' => CallService::route('/{record}/call'),
        ];
    }
}
