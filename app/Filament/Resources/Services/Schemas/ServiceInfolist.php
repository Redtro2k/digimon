<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Classes\ServiceSection;
use App\Livewire\Timer;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(5)
            ->components([
                Grid::make()
                    ->columnSpan(3)
                    ->columns(1)
                    ->schema([
                        ServiceSection::customer(),
                        ServiceSection::vehicle(),
                        ServiceSection::service(),
                    ]),
                Grid::make()
                    ->columnSpan(2)
                    ->columns(1)
                    ->schema([
                        Section::make('Quick Action')
                            ->hidden(fn() => auth()->user()->can('mark_as_arrived'))
                            ->icon(LucideIcon::Zap)
                            ->iconColor('primary')
                            ->schema(fn($record) => [
                                Livewire::make(Timer::class, [
                                    'record' => $record
                                ])
                            ])
                            ->description('Key details about the quick actions.'),
                       ServiceSection::reminder(),
                    ])
            ]);
    }
}
