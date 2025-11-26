<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\MRASAppointment;
use App\Livewire\GeneratedForcast;
use App\Livewire\MrasPerCustomerCalled;
use App\Livewire\MRASStatsWidgets;
use App\Livewire\PerDealer;
use App\Livewire\PerMras;
use App\Livewire\PerMrasCalledStatistics;
use App\Livewire\UnsuccessfulBooked;
use App\Livewire\UnsuccessfulCall;
use App\Livewire\UserLogHistoryWidgets;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    protected static ?string $title = 'Your Dashboard';

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->schema([
                    Select::make('preset')
                        ->label('Quick Range')
                        ->options([
                            'today' => 'Today',
                            'this_week' => 'This Week',
                            'this_month' => 'This Month',
                            'this_year' => 'This Year',
                            'custom' => 'Custom Range',
                        ])
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            match ($state) {
                                'today' => [
                                    $set('startDate', Carbon::today()),
                                    $set('endDate', Carbon::today()),
                                ],
                                'this_week' => [
                                    $set('startDate', Carbon::now()->startOfWeek()),
                                    $set('endDate', Carbon::now()->endOfWeek()),
                                ],
                                'this_month' => [
                                    $set('startDate', Carbon::now()->startOfMonth()),
                                    $set('endDate', Carbon::now()->endOfMonth()),
                                ],
                                'this_year' => [
                                    $set('startDate', Carbon::now()->startOfYear()),
                                    $set('endDate', Carbon::now()->endOfYear()),
                                ],
                                default => [
                                    $set('startDate', null),
                                    $set('endDate', null),
                                ]
                            };
                        }),
                    DatePicker::make('startDate')->native(false)->maxDate(fn ($get) => $get('endDate')),
                    DatePicker::make('endDate')->native(false)->minDate(fn ($get) => $get('startDate')),
                ]),
        ];
    }
    public function getColumns(): int|array
    {
        return 3;
    }
    public function getSubheading(): ?string
    {
        $user = auth()->user();
        $greeting = match (true) {
            now()->hour < 12 => 'Amazing Morning,',
            now()->hour < 18 => 'Amazing Afternoon,',
            default => 'Amazing Evening,',
        };
        return $greeting.' ' . $user->gender->title().$user->name. ' — here are your stats for today.';
    }

    public function getWidgets(): array
    {
        return [
            MRASStatsWidgets::class,
            MrasPerCustomerCalled::class,
            MRASAppointment::class,
            GeneratedForcast::class,
            UnsuccessfulCall::class,
            UnsuccessfulBooked::class,
            PerDealer::class,
            PerMras::class,
            UserLogHistoryWidgets::class,
            PerMrasCalledStatistics::class
        ];
    }

}
