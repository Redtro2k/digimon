<?php

namespace App\Filament\Resources\LeadTimes\Tables;

use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Spatie\Activitylog\Models\Activity;

class LeadTimesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->disabledSelection()
            ->modifyQueryUsing(fn($query) => $query
                ->when(auth()->user()->hasRole('mras'), function($q){
                    $q->where('causer_id', auth()->id());
                })
            )
            ->columns([
                TextColumn::make('event')
                    ->label('Event Name')
                    ->badge()
                    ->color(fn($state) => match($state){
                        'login' => 'success',
                        'logout' => 'danger',
                        'calling' => 'primary',
                        'idle' => 'info',
                        'hangup' => Color::Rose,
                        default => 'gray'
                    }),
                TextColumn::make('description')
                    ->html()
                    ->label('Content'),
                TextColumn::make('causer_id')
                    ->label('User')
                    ->badge()
                    ->color('secondary')
                    ->formatStateUsing(fn($state) => User::find($state)->name ?? 'No User'),
                TextColumn::make('created_at')
                    ->label('Logged At')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => Carbon::parse($state)->format('M d, Y h:i A'))
            ])
            ->filters([
                DateRangeFilter::make('created_at'),
                SelectFilter::make('causer_id')
                    ->label('Select MRAS')
                    ->multiple()
                    ->options(fn() => User::role('mras')->pluck('name', 'id')->toArray()),
                SelectFilter::make('event')
                    ->label('Event')
                    ->native(false)
                    ->options(fn() => Activity::all()->pluck('event', 'event')),
            ], layout: FiltersLayout::AboveContent)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
