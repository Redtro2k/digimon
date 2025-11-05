<?php

namespace App\Filament\Resources\Services\Tables;

use App\Classes\ServiceAction;
use App\Models\User;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
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
            })
            ->deferLoading()
            ->striped()
            ->headerActions([
                ServiceAction::make()
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
                    DeleteBulkAction::make(),
                    BulkAction::make('assignedServices')
                        ->label('Assigned to MRAS')
                        ->visible(fn() => auth()->user()->hasRole('super_admin'))
                        ->icon(LucideIcon::UserPlus2)
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('Assigned to MRAS')
                        ->modalDescription('Would you like to automatically assign these services to MRAS users now?')
                        ->action(function(Collection $records, array $data){

                            if($records->contains(fn($service) => $service->assigned_id !== null)){
                                Notification::make()
                                    ->title('Assigned Failed')
                                    ->body('Some Selected service are already assigned to an MRAS user.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            $mras = User::role('mras')->get();
                            $shuffled = $records->shuffle();

                            $chunks = $shuffled->split($mras->count());

                            foreach($mras as $mra => $user){
                                foreach($chunks[$mra] ?? [] as $service){
                                    $service->update(['assigned_mras_id' => $user->id]);

                                    Notification::make()
                                        ->title('New Services')
                                        ->body('You have a new Services assigned by the System')
                                        ->info()
                                        ->sendToDatabase($user, isEventDispatched: true)
                                        ->broadcast($user);
                                }
                            }

                            Notification::make()
                                ->title('Assigned Successful')
                                ->body('Selected services have been successfully assigned to MRAS users.')
                                ->success()
                                ->send();
                        })
                ]),
            ])
            ->extremePaginationLinks()
            ->defaultPaginationPageOption(5);
    }
}
