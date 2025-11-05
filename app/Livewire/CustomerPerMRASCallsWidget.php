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
        return auth()->user()->hasAnyRole('super_admin', 'manager');
    }
    protected function getTablePage(): string
    {
        return ListServices::class;
    }
    protected function getStats(): array
    {
        return User::role('mras')
            ->get()
            ->map(function ($user) {
                $called = Service::query()
                    ->where('assigned_mras_id', $user->id)
                    ->whereHas('reminders', function ($query) {
                        $query->whereBetween('created_at', [
                            today()->startOfDay(),
                            today()->endOfDay()
                        ]);
                    })
                    ->count();

//                $stats = $user->serviceReminder()

                return Stat::make(
                    $user->name . ' (' . $user->load('dealer')->dealer->first()?->acronym . ')',
                    $this->getPageTableQuery()
                        ->whereHas('assignedMras', function($query) use($user){
                            $query->where('assigned_mras_id', $user->id)
                                ->whereNot('has_completed', true);
                        })
                        ->count())
                    ->icon(LucideIcon::User)
                    ->descriptionColor($called > 0 ? 'success' : 'danger')
                    ->descriptionIcon(LucideIcon::TrendingUp, IconPosition::Before)
                    ->chart([])
                    ->chartColor('primary')
                    ->description($called > 0 ? "has {$called} called for today" : null);
            })
            ->all();
    }
}
