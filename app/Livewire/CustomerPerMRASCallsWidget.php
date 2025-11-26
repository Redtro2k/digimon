<?php

namespace App\Livewire;

use App\Filament\Resources\Services\Pages\ListServices;
use App\Models\Service;
use App\Models\User;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomerPerMRASCallsWidget extends BaseWidget
{
    use InteractsWithPageTable;

    protected ?string $heading = 'MRAS Analytics';

    protected ?string $description = 'An overview of  MRAS called analytics.';

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole('super_admin', 'manager', 'service_admin');
    }

    protected function getTablePage(): string
    {
        return ListServices::class;
    }

    protected function getStats(): array
    {
        // Get the current tab from URL
        $currentTab = request()->query('tab');

        // Check if any attempt filter is applied
        $hasAttemptFilter = in_array($currentTab, ['first_attempt', 'second_attempt', 'third_attempt', 'final_result']);

        return User::role('mras')
            ->when(auth()->user()->hasRole('service_admin'), function ($query) {
                $userDealerId = auth()->user()->dealer()->first()?->id;
                return $query->whereHas('dealer', function($query) use($userDealerId) {
                    $query->where('dealers.id', $userDealerId);
                });
            })
            ->get()
            ->map(function ($user) use ($hasAttemptFilter) {

                $called = Service::query()
                    ->where('assigned_mras_id', $user->id)
                    ->whereHas('reminders', function ($query) {
                        $query->whereBetween('created_at', [
                            today()->startOfDay(),
                            today()->endOfDay()
                        ]);
                    })
                    ->count();

                $assigned = $this->getPageTableQuery()
                    ->whereHas('assignedMras', function($query) use($user){
                        $query->where('assigned_mras_id', $user->id)
                            ->whereNot('has_completed', true);
                    })
                    ->count();

                $average = $assigned > 0 ? number_format(($called / $assigned) * 100, 2) : 0;

                return Stat::make(
                    $user->name . ' (' . $user->load('dealer')->dealer->first()?->acronym . ')',
                    $assigned)
                    ->icon(LucideIcon::User)
                    ->descriptionColor($called > 0 ? 'success' : 'danger')
                    ->descriptionIcon(LucideIcon::TrendingUp, IconPosition::Before)
                    ->chart([])
                    ->chartColor('primary')
                    ->description($called > 0 && !$hasAttemptFilter ? "has {$called} called for today, with an average of {$average}%" : null);
            })
            ->all();
    }
}
