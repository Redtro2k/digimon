<?php

namespace App\Filament\Resources\Dealers;

use App\Filament\Resources\Dealers\Pages\CreateDealer;
use App\Filament\Resources\Dealers\Pages\EditDealer;
use App\Filament\Resources\Dealers\Pages\ListDealers;
use App\Filament\Resources\Dealers\Schemas\DealerForm;
use App\Filament\Resources\Dealers\Tables\DealersTable;
use App\Models\Dealer;
use App\NavigationGroup;
use BackedEnum;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DealerResource extends Resource
{
    protected static ?string $model = Dealer::class;

    protected static string|BackedEnum|null $navigationIcon = LucideIcon::Building;
    protected static string|null|\UnitEnum $navigationGroup = NavigationGroup::MIS;
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationBadgeTooltip = 'The number of dealers';



    protected static ?string $recordTitleAttribute = 'acronym';

    public static function form(Schema $schema): Schema
    {
        return DealerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DealersTable::configure($table);
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
            'index' => ListDealers::route('/'),
            'create' => CreateDealer::route('/create'),
            'edit' => EditDealer::route('/{record}/edit'),
        ];
    }
}
