<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Resources\Services\ServiceResource;
use App\Livewire\CustomerPerMRASCallsWidget;
use App\Models\Service;
use Carbon\Carbon;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Actions\CreateAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListServices extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $isMras = auth()->user()->hasRole('mras');
        $userId = auth()->id();

        return [
            'all' => Tab::make('Available')
                ->icon(LucideIcon::Users2)
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->incomplete()
                    ->when($isMras, fn(Builder $q) => $q->whereDoesntHave('reminders'))
                )
                ->badge(fn() => $this->getAvailableCount($isMras)),

            'n_minus_7' => Tab::make('N-7')
                ->icon(LucideIcon::Calendar1)
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->incomplete()
                    ->whereBetween('forecast_date', $this->getN7DateRange())
                    ->when($isMras, fn(Builder $q) => $q
                        ->where('assigned_mras_id', $userId)
                        ->whereDoesntHave('reminders')
                    )
                )
                ->badge(fn() => $this->getN7Count($isMras, $userId)),

            'first_attempt' => Tab::make('1st Attempt')
                ->icon(LucideIcon::Users2)
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->incomplete()
                    ->whereHas('reminders', fn($q) => $q->firstAttempt())
                    ->when($isMras, fn(Builder $q) => $q->where('assigned_mras_id', $userId))
                )
                ->badge(fn() => $this->getAttemptCount('firstAttempt', $isMras, $userId)),

            'second_attempt' => Tab::make('2nd Attempt')
                ->icon(LucideIcon::Users2)
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->incomplete()
                    ->whereHas('reminders', fn($q) => $q->secondAttempt())
                    ->when($isMras, fn(Builder $q) => $q->where('assigned_mras_id', $userId))
                )
                ->badge(fn() => $this->getAttemptCount('secondAttempt', $isMras, $userId)),

            'third_attempt' => Tab::make('3rd Attempt')
                ->icon(LucideIcon::Users2)
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->incomplete()
                    ->whereHas('reminders', fn($q) => $q->thirdAttempt())
                    ->when($isMras, fn(Builder $q) => $q->where('assigned_mras_id', $userId))
                )
                ->badge(fn() => $this->getAttemptCount('thirdAttempt', $isMras, $userId)),

            'final_attempt' => Tab::make('Final Result')
                ->icon(LucideIcon::CheckCheck)
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->where('has_completed', true)
                    ->when($isMras, fn(Builder $q) => $q->where('assigned_mras_id', $userId))
                )
                ->badge(fn() => $this->getCompletedCount($isMras, $userId)),
        ];
    }

    /**
     * Get the date range for N-7 calculation
     */
    protected function getN7DateRange(): array
    {
        return [
            Carbon::today()->subDays(7)->startOfDay(),
            Carbon::today()->endOfDay(),
        ];
    }

    /**
     * Get count for Available tab
     */
    protected function getAvailableCount(bool $isMras): int
    {
        return Service::query()
            ->incomplete()
            ->when($isMras, fn(Builder $q) => $q->where('assigned_mras_id', auth()->id())->whereDoesntHave('reminders'))
            ->count();
    }

    /**
     * Get count for N-7 tab
     */
    protected function getN7Count(bool $isMras, int $userId): int
    {
        return Service::query()
            ->incomplete()
            ->whereBetween('forecast_date', $this->getN7DateRange())
            ->when($isMras, fn(Builder $q) => $q
                ->where('assigned_mras_id', $userId)
                ->whereDoesntHave('reminders')
            )
            ->count();
    }

    /**
     * Get count for attempt tabs
     */
    protected function getAttemptCount(string $attemptScope, bool $isMras, int $userId): int
    {
        return Service::query()
            ->incomplete()
            ->whereHas('reminders', fn($q) => $q->$attemptScope())
            ->when($isMras, fn(Builder $q) => $q->where('assigned_mras_id', $userId))
            ->count();
    }

    /**
     * Get count for completed services
     */
    protected function getCompletedCount(bool $isMras, int $userId): int
    {
        return Service::query()
            ->where('has_completed', true)
            ->when($isMras, fn(Builder $q) => $q->where('assigned_mras_id', $userId))
            ->count();
    }
    public function getHeaderWidgets(): array
    {
        return [
            CustomerPerMRASCallsWidget::class,
        ];
    }
}
