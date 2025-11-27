<?php

namespace App\Filament\Resources\Services\Tables;

use App\Classes\ServiceAction;
use App\Filament\Exports\ServiceExporter;
use App\Jobs\AssigneCustomerJob;
use App\Models\Dealer;
use App\Models\User;
use Carbon\Carbon;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Fieldset;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ServicesTable
{

    public static function configure(Table $table): Table
    {
        return $table
            ->emptyStateDescription('Once have a  Services, it will appear here.')
            ->modifyQueryUsing(function($query){
                $query->when(auth()->user()->hasRole('mras'), function($q){
                    $q->where('assigned_mras_id', auth()->id());
                });
                $query->when(auth()->user()->hasRole('receptionist'), function($q){
                    $user = auth()->user()->dealer()->pluck('dealer_id'); //current user
                    $dealer = Dealer::with(['users' => fn($q) => $q->role('mras')])->whereIn('id', [$user])->get();

                   $q->has('latestReminder')->whereIn('assigned_mras_id', $dealer->flatMap(fn($d) => $d->users->pluck('id')));
                });
                $query->when(!auth()->user()->hasAnyRole(['super_admin', 'manager']), function($q){
                    $q->where('dealer_id', auth()->user()->dealer()->first()?->id);
                });
            })
            ->deferLoading()
            ->striped()
            ->headerActions([
                ServiceAction::make(),
                ServiceAction::exports(),
                ServiceAction::resultSummary()
            ])
            ->searchPlaceholder('Search (Plate, Model)')
            ->filtersFormColumns(3)
            ->persistFiltersInSession()
            ->columns([
                ColumnGroup::make('Customer Information', [
                    TextColumn::make('customer.source')
                        ->label('Source')
                        ->searchable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('customer.customer_name')
                        ->label('Customer Name')
                        ->searchable()
                        ->toggleable(),
                    TextColumn::make('customer.mobile_number')
                        ->label('Mobile Number')
                        ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('customer.address')
                        ->label('Address')
                        ->searchable()
                        ->placeholder('No Address')
                        ->toggleable(isToggledHiddenByDefault: true),
                ]),
                ColumnGroup::make('Vehicle Information', [
                    TextColumn::make('vehicle.model')
                        ->label('Model')
                        ->searchable()
                        ->toggleable(),
                    TextColumn::make('vehicle.plate')
                        ->label('Plate')
                        ->searchable(),
                    TextColumn::make('vehicle.cs_number')
                        ->label('CS No.')
                        ->searchable(),
                    TextColumn::make('last_service_availed')
                        ->label('Recommended PM Service')
                        ->placeholder('No  PM Service')
                        ->formatStateUsing(fn($state) => is_numeric($state) ? number_format((double)$state).'KM CHECK-UP' : $state)
                        ->alignment(Alignment::Center)
                        ->toggleable(),
                    TextColumn::make('recommended_pm_service')
                        ->label('Recommended PM Service')
                        ->placeholder('No  PM Service')
                        ->formatStateUsing(fn($state) => is_numeric($state) ? number_format((double)$state).'KM CHECK-UP' : $state)
                        ->alignment(Alignment::Center)
                        ->toggleable(),
                ]),
                ColumnGroup::make('Services Information', [
                    TextColumn::make('forecast_status')
                        ->badge()
                        ->alignment(Alignment::Center)
                        ->label('Forecast Status'),
                    TextColumn::make('has_fpm')
                        ->badge()
                        ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No')
                        ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                        ->toggleable(),
                    TextColumn::make('forecast_date')
                        ->label('Forecast Date')
                        ->date(),
                ]),
                ColumnGroup::make('1st Attempt', [
                    TextColumn::make('first_reminder.category.name')
                        ->label('Category')
                        ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('first_reminder.sub_result')
                        ->label('Sub Result')->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('first_reminder.call_back')
                        ->datetime()
                        ->label('Call Back')->toggleable(isToggledHiddenByDefault: true),
                ]),
                ColumnGroup::make('2nd Attempt', [
                    TextColumn::make('second_reminder.category.name')
                        ->label('Category')->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('second_reminder.sub_result')
                        ->label('Sub Result')->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('second_reminder.call_back')
                        ->datetime()
                        ->label('Call Back')->toggleable(isToggledHiddenByDefault: true),
                ]),
                ColumnGroup::make('3rd Attempt', [
                    TextColumn::make('third_reminder.category.name')->toggleable(isToggledHiddenByDefault: true)
                        ->label('Category'),
                    TextColumn::make('third_reminder.sub_result')->toggleable(isToggledHiddenByDefault: true)
                        ->label('Sub Result'),
                    TextColumn::make('third_reminder.call_back')->toggleable(isToggledHiddenByDefault: true)
                        ->datetime()
                        ->label('Call Back'),
                ]),
                ColumnGroup::make('Final Result Reminder', [
                    TextColumn::make('latestReminder.sub_result')
                        ->label('Status')
                        ->badge()
                        ->color(fn($state) => $state->getColor())
                        ->formatStateUsing(fn($state) => $state->getLabel())
                        ->icon(fn($state) => $state->getIcon())
                        ->alignment(Alignment::Center)
                        ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('latestReminder.category.name')
                        ->label('Sub Result')
                        ->placeholder('No  Call Back Date')
                        ->toggleable(isToggledHiddenByDefault: true),
                    TextColumn::make('latestReminder.call_back')
                        ->label('Call Back Date')
                        ->dateTime()
                        ->placeholder('No  Call Back Date')
                        ->toggleable(isToggledHiddenByDefault: true),
                ]),
                TextColumn::make('assignedMras.name')
                    ->placeholder('Not Assigned')
                    ->label('Assigned to')
                    ->badge()
                    ->color(Color::Teal)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //

            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                ])
                ->label('Actions'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->hidden(auth()->user()->can('Delete:Service')),
                    BulkAction::make('assignedServices')
                        ->label('Assigned to MRAS')
                        ->visible(fn() => auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('service_admin'))
                        ->icon(LucideIcon::UserPlus2)
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Assigned to MRAS')
                        ->modalDescription('Would you like to automatically assign these services to MRAS users now?')
                        ->action(function(Collection $records){
                            AssigneCustomerJob::dispatch($records, auth()->user());
                        }),
                ]),
            ])
            ->extremePaginationLinks()
            ->defaultPaginationPageOption(5);
    }
}
