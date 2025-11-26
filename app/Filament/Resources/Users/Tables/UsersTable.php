<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function($query){
                $query->when(!auth()->user()->hasRole('super_admin'), function($query){
                    $query->roles->whereNotIn('name', ['super_admin']);
                });
            })
            ->columns([
                ImageColumn::make('profile')
                    ->circular()
                    ->getStateUsing(fn($record) => $record->profile ? Storage::disk('public')->url($record->profile) : $record->gender->avatar() . str($record->name)
                        ->headline()
                        ->replace(' ', '+')),
                TextColumn::make('username')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->formatStateUsing(fn ($state) => Str::headline($state))
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->getStateUsing(fn($record) => $record->roles->pluck('name')
                        ->map(fn($role) => Str::headline($role))
                        ->implode(', '))
                    ->badge(),
                TextColumn::make('dealer.acronym')
                    ->badge(),
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
                EditAction::make(),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
