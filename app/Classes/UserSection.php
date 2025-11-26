<?php

namespace App\Classes;


use App\Models\User;
use Carbon\CarbonInterval;
use CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\FontWeight;
use Spatie\Activitylog\Models\Activity;

class UserSection
{
    public static function statistics(): array{
        return [
               TextEntry::make('id')
                   ->label('Called Customer Today')
                   ->weight(FontWeight::Bold)
                   ->color('primary')
                   ->icon(LucideIcon::User2)
                   ->iconColor('primary')
                   ->formatStateUsing(function($state){
                       $user = User::find($state);
                       return $user->load('serviceReminders')->serviceReminders()->whereDate('reminders.created_at', today())->pluck('service_id')->unique()->count(). ' Customer';
                   }),
                TextEntry::make('id')
                    ->label('Overall Average Today')
                    ->formatStateUsing(function($state) {
                        $hangupActivities = Activity::where('event', 'hangup')
                            ->whereDate('created_at', today())
                            ->where('causer_id', $state)
                            ->get();

                        if ($hangupActivities->isEmpty()) {
                            return '0%';
                        }

                        $totalSeconds = self::getTotalCallSeconds($state);
                        // $averageMinutes = $totalSeconds / $hangupActivities->count() / 60;
                        // return number_format($averageMinutes, 2) . '%';

                        $averageMinutes = $totalSeconds / $hangupActivities->count();
                        $interval = CarbonInterval::seconds($averageMinutes)->cascade();

                        return $interval->forHumans();
                    })
                    ->weight(FontWeight::Bold)
                    ->color('primary')
                    ->icon(LucideIcon::User2)
                    ->iconColor('primary'),
                TextEntry::make('id')
                    ->label('Overall Called Today')
                    ->formatStateUsing(function($state) {
                        $totalSeconds = self::getTotalCallSeconds($state);

                        if ($totalSeconds === 0) {
                            return '0 seconds';
                        }

                        $interval = CarbonInterval::seconds($totalSeconds)->cascade();
                        return $interval->forHumans();
                    })
                    ->weight(FontWeight::Bold)
                    ->color('primary')
                    ->icon(LucideIcon::User2)
                    ->iconColor('primary'),
            TextEntry::make('id')
                ->label('Overall Remaining Customer')
                ->weight(FontWeight::Bold)
                ->color('primary')
                ->icon(LucideIcon::User2)
                ->iconColor('primary')
                ->columnSpan(2)
                ->formatStateUsing(function($state){
                    return User::find($state)->assignedService->count(). ' Customer';
                }),
            ];
    }

    protected static function getTotalCallSeconds($id){ // averageMinutes = (totalSeconds ÷ numberOfCalls) ÷ 60

        return Activity::query()
            ->where('event', 'hangup')
            ->whereDate('created_at', today())
            ->where('causer_id', $id)
            ->get()
            ->sum(function ($activity) {
                $duration = $activity->properties['duration'] ?? '00:00:00';

                if (preg_match('/(\d{2}):(\d{2}):(\d{2})/', $duration, $matches)) {
                    return ($matches[1] * 3600) + ($matches[2] * 60) + $matches[3];
                }
                return 0;
            });
    }
}
