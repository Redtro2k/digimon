<?php
namespace App\Filament\Resources\Users\Schemas;

use App\Classes\UserSection;
use App\Livewire\CustomerPerMRASCallsWidget;
use App\Livewire\Feeds;
use App\Livewire\MRAScalledToday;
use App\Models\User;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\IconSize;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $id = request()->route('record');

        return $schema
            ->columns(5)
            ->schema([
                Grid::make()
                    ->columnSpan(3)
                    ->columns(1)
                    ->schema([
                        Section::make('Account Information')
                            ->icon(LucideIcon::Users2)
                            ->iconColor('primary')
                            ->iconSize(IconSize::Large)
                            ->description('Essential user profile data and account settings')
                            ->schema([
                                ImageEntry::make('user_avatar')
                                    ->hiddenLabel()
                                    ->alignCenter()
                                    ->columnSpanFull()
                                    ->circular()
                                    ->getStateUsing(fn($record) => $record->profile ? Storage::disk('public')->url($record->profile) : $record->user_avatar),
                                Grid::make()
                                    ->columnSpan(4)
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('name')
                                            ->color('primary'),
                                        TextEntry::make('email')
                                            ->color('primary'),
                                        TextEntry::make('dealer.acronym')
                                            ->label('Dealer')
                                            ->color('primary')
                                            ->badge(),
                                        TextEntry::make('roles.name')
                                            ->label('Role')
                                            ->color('primary')
                                            ->badge(),
                                    ])
                            ])
                            ->collapsible()
                            ->persistCollapsed(),
                        Livewire::make(MRAScalledToday::class, ['id' => $id])
                            ->visible(fn($record) => $record->hasRole('mras'))
                        ,
                    ]),
                Grid::make()
                    ->columnSpan(2)
                    ->columns(1)
                    ->schema([
                        Section::make('Performance Metrics')
                            ->visible(fn($record) => $record->hasRole('mras'))
                            ->description('User performance metrics')
                            ->iconColor('primary')
                            ->icon(LucideIcon::TrendingUp)
                            ->collapsible()
                            ->persistCollapsed()
                            ->columns(3)
                            ->schema(UserSection::statistics()),
                        Section::make('Log Activity')
                            ->description('View all user activities, changes, and system events for audit and compliance tracking.')
                            ->icon(LucideIcon::History)
                            ->iconColor('primary')
                            ->footerActionsAlignment(Alignment::Center)
                            ->schema(function() {
                                $id = request()->route('record');
                                $activities = Activity::query()
                                    ->whereToday('created_at')
                                    ->where('causer_id', $id);

                                return [
                                    Livewire::make(Feeds::class, [
                                        'activities' => $activities
                                            ->get()
                                            ->map(fn($activity) => [
                                                'event' => $activity->event,
                                                'causer' => $activity?->load('causer')->causer,
                                                'description' => $activity->description,
                                                'subject_id' => $activity?->subject_id,
                                                'created_at' => $activity->created_at,
                                            ]),
                                    ])
                                ];
                            })
                    ])

            ]);
    }
}
