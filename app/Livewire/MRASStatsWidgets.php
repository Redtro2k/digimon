<?php

namespace App\Livewire;

use App\Enums\StatusEnum;
use App\Models\Service;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use NunoMaduro\Collision\Adapters\Phpunit\State;

class MRASStatsWidgets extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    public static function canView(): bool
    {
        return auth()->user()->hasRole('mras');
    }
    protected function getStats(): array
    {
        $startDate = $this->pageFilters['startDate'] ?? null;
        $endDate = $this->pageFilters['endDate'] ?? null;

        $customer_call = Service::query()
            ->where('assigned_mras_id', auth()->id())
            ->where('has_completed', false)
            ->whereDoesntHave('latestReminder')
            ->when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate))
            ->count();

        $customer_reached = $this->perStatusCall('successful', $startDate, $endDate);
        $customer_unreached = $this->perStatusCall('unsuccessful', $startDate, $endDate);

        $attempted = $customer_reached + $customer_unreached;
        $successRate = $attempted > 0 ? round(($customer_reached / $attempted) * 100, 1) : 0;

        return [
            //
            Stat::make('Not Yet Contacted', $customer_call)
                ->description('These customers have been assigned to you for a call.')
                ->color('primary'),
            Stat::make('Customers You’ve Reached', $customer_reached)
                ->description('Total number of customers you have successfully called.')
                ->color('success'),
            Stat::make('Unanswered Customers', $customer_unreached)
                ->description('These are the customers you tried to call but couldn’t reach.')
                ->color('danger'),
            Stat::make('Success Rate', $successRate . '%')
                ->description('Percentage of answered calls out of all attempts made.')
                ->color(match (true) {
                    $successRate >= 80 => 'success',
                    $successRate >= 50 => 'warning',
                    default => 'danger',
                })
                ->descriptionIcon(LucideIcon::ChartBar),
        ];
    }

    public function perStatusCall($status, $startDate = null, $endDate = null): int|Service
    {
        return Service::query()
            ->where('assigned_mras_id', auth()->id())
            ->whereHas('latestReminder', function (Builder $query) use($startDate, $endDate, $status) {
                $query
                    ->where('sub_result', StatusEnum::from($status)->value)
                    ->when($startDate, fn (Builder $query) => $query->whereDate('created_at', '>=', $startDate))
                    ->when($endDate, fn (Builder $query) => $query->whereDate('created_at', '<=', $endDate));
            })
            ->count();
    }
}
