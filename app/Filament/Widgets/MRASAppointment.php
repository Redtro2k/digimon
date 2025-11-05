<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Services\ServiceResource;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Service;

class MRASAppointment extends TableWidget
{
    protected static ?string $heading = "Your Customer's Reminded";
    public static function canView(): bool
    {
        return auth()->user()->hasRole('mras');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Service::query()
                ->whereHas('latestReminder', function (Builder $query) {
                    return $query
                        ->where('sub_result', 'successful')
                        ->whereNotNull('call_back');
                })
                ->with('customer'))
            ->deferLoading()
            ->striped()
            ->defaultSort('created_at')
            ->columns([
                TextColumn::make('vehicle.plate')
                    ->label('Plate No.')
                    ->limit(13)
                    ->searchable(),
                TextColumn::make('latestReminder.category.name'),
                TextColumn::make('latestReminder.call_back')
                    ->label('Schedule & Callback Date')
                    ->dateTime('M d, Y h:i A'),

            ])
            ->filters([
                //
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
