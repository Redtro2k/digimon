<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Services\ServiceResource;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Service;

class MRASAppointment extends TableWidget
{
    protected static ?string $heading = "Your Customer's Reminded";
    protected int | string | array $columnSpan = 2;
    public static function canView(): bool
    {
        return auth()->user()->hasRole('mras');
    }

    public function table(Table $table): Table
    {
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;

        return $table
            ->query(fn () => Service::query()
                ->whereHas('latestReminder', function (Builder $query) use($startDate, $endDate) {
                    $query->when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
                        ->when($endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate))
                        ->when($startDate === null && $endDate === null, fn (Builder $query) => $query->whereToday('created_at'));
                })
                ->where('assigned_mras_id', auth()->id()))
            ->deferLoading()
            ->striped()
            ->defaultSort('created_at')
            ->columns([
                TextColumn::make('vehicle.plate')
                    ->label('Plate No.')
                    ->limit(13)
                    ->searchable(),
                TextColumn::make('latestReminder.category.name'),
                TextColumn::make('latestReminder.sub_result')
                    ->badge()
                    ->color(fn($state) => $state->getColor())
                    ->icon(fn($state) => $state->getIcon())
                    ->label('Sub Result(Call Status)')
                    ->formatStateUsing(fn($state) => $state->getLabel()),
                TextColumn::make('latestReminder.call_back')
                    ->placeholder('N/A')
                    ->label('Schedule & Callback Date')
                    ->dateTime('M d, Y h:i A'),

            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn ($record) => ServiceResource::getUrl('view', ['record' => $record]))
                    ->icon(LucideIcon::User2)
                    ->label('More Details'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
