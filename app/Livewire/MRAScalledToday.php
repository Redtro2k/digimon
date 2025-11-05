<?php

namespace App\Livewire;

use App\Enums\StatusEnum;
use App\Models\Category;
use App\Models\Reminder;
use App\Models\Service;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;


class MRAScalledToday extends TableWidget
{
    protected static ?string $heading = "Today's Customer Called";

    public $id;
    protected function getTableQuery(): Builder
    {
        return Service::query()
            ->where('assigned_mras_id', $this->id)
            ->has('reminders');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('latestReminder.attempt')
                    ->label('Attempt')
                    ->formatStateUsing(fn($state) => $state->getLabel()),
                    TextColumn::make('customer.customer_name')
                        ->label('Name')
                        ->searchable()
                        ->toggleable(),
                    TextColumn::make('vehicle.plate')
                        ->label('Plate')
                        ->searchable(),
                    TextColumn::make('latestReminder.sub_result')
                        ->label('Status Called')
                        ->badge()
                        ->color(fn($state) => $state->getColor())
                        ->icon(fn($state) => $state->getIcon())
                        ->alignment(Alignment::Center),
                    TextColumn::make('latestReminder.category.name')
                        ->label('Called Result'),
                    TextColumn::make('latestReminder.call_back')
                        ->label('Call Back or Schedule')
                        ->dateTime('M d, Y h:i A')
            ])
            ->filters([
                //
                SelectFilter::make('latestReminder.sub_result')
                    ->options(fn() => Category::query()
                        ->pluck('status', 'status')
                        ->mapWithKeys(fn($category) => [$category->value => ucwords($category->value)])
                    ),
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
