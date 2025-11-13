<x-filament-widgets::widget>
    <x-filament::section
        heading="Log Activity"
        icon-color="primary"
        :icon="\CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon::TrendingUp"
        description="View all your activities, changes, and system events for audit and compliance tracking.">
            <livewire:feeds
                :activities="Spatie\Activitylog\Models\Activity::query()
                ->where('causer_id', auth()->id())
                ->latest()
                ->get()
                ->map(fn ($activity) => [
                    'event' => $activity->event,
                    'causer' => $activity?->load('causer')->causer,
                    'description' => $activity->description,
                    'subject_id' => $activity?->subject_id,
                    'created_at' => $activity->created_at,
                ])
            "
            />
    </x-filament::section>
</x-filament-widgets::widget>
