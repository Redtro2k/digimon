<div class="flow-root">
    @if($activities->isEmpty())
        <x-filament::empty-state
            heading="No Activities yet"
            :icon="\CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon::Inbox"
            description="There are no activities to display right now. New logs will appear here action are recorded."
        />
    @else
        <div class="overflow-y-auto max-h-96">
            @forelse($activities as $date => $dailyActivities)
                <div>
                    <h3 class="text-sm font-semibold text-center text-gray-600 dark:text-gray-400 mb-2">
                        {{ $date }}
                    </h3>
                    <ul role="list" class="mb-8">
                        @foreach($dailyActivities as $key => $activity)
                            @php
                                $event = $activity['event'];
                                $isLast = count($dailyActivities) == $key + 1;
                                $icons = [
                                    'login' => \CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon::LogIn,
                                    'logout' => \CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon::LogOut,
                                    'idle' => \CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon::Clock,
                                    'calling' => \CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon::PhoneCall,
                                    'hangup' => \CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon::PhoneOff,
                                ];
                            @endphp

                            <li>
                                <div class="relative pb-8">
                                    @unless($isLast)
                                        <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                    @endunless

                                    {{-- SWITCH FOR EVENT TYPE --}}
                                    @switch($event)
                                        {{-- LOGIN / LOGOUT / IDLE --}}
                                        @case('login')
                                        @case('logout')
                                        @case('idle')
                                            <div class="relative flex items-start space-x-2">
                                                <div class="relative">
                                                    <img class="flex size-10 items-center justify-center rounded-full bg-gray-400 dark:bg-gray-600 ring-8 ring-white dark:ring-gray-900"
                                                         src="{{ $activity['causer']->profile ? Storage::disk('public')->url($activity['causer']->user_avatar) :$activity['causer']->user_avatar }}" alt="">
                                                    <span class="absolute -right-1 -bottom-0.5 rounded-tl bg-white dark:bg-gray-900 px-0.5 py-px">
                                                        <x-filament::icon :icon="$icons[$event]" class="size-5 p-0.5 text-gray-500 dark:text-gray-400"/>
                                                    </span>
                                                </div>

                                                <div class="min-w-0 flex-1">
                                                    <div>
                                                        <div class="text-sm">
                                                            <x-filament::link :href="route('filament.digimon.resources.users.view', $activity['causer']->id)"
                                                                              class="font-medium text-gray-900 dark:text-gray-100">
                                                                {{ $activity['causer']->name }}
                                                            </x-filament::link>
                                                        </div>
                                                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                                            {{ ucfirst($event) }} {{ \Carbon\Carbon::parse($activity['created_at'])->diffForHumans() }}
                                                        </p>
                                                    </div>
                                                    <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                                        <p>{!! $activity['description'] !!}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            @break

                                            {{-- CALLING / HANGUP --}}
                                        @case('calling')
                                        @case('hangup')
                                            <div class="relative flex items-start space-x-3">
                                                <div class="relative px-1">
                                                    <div class="flex size-8 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 ring-8 ring-white dark:ring-gray-900">
                                                        <x-filament::icon :icon="$icons[$event]" class="size-5 text-gray-500 dark:text-gray-400 p-0.5"/>
                                                    </div>
                                                </div>

                                                <div class="min-w-0 flex-1 py-1.5">
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {!! str_replace(
                                                            ['<strong>', '</strong>'],
                                                            ['<a href="'.route('filament.digimon.resources.services.view', $activity['subject_id']).'" class="text-primary-600 hover:underline font-bold"><strong>', '</strong></a>'],
                                                            $activity['description']
                                                        ) !!}
                                                        <span class="whitespace-nowrap">{{ \Carbon\Carbon::parse($activity['created_at'])->diffForHumans() }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            @break
                                    @endswitch
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <x-filament::empty-state
                    heading="No Activities yet"
                    :icon="\CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon::Inbox"
                    description="There are no activities to display right now. New logs will appear here once actions are recorded."
                />
            @endforelse
        </div>
    @endif

</div>
