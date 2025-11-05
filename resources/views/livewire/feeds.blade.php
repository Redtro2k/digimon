<div class="flow-root">
    @if($activities->isEmpty())
        <x-filament::empty-state
            heading="No Activities yet"
            :icon="\CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon::Inbox"
            description="There are no activities to display right now. New logs will appear here action are recorded."
        />
    @else
        <div class="overflow-y-auto max-h-96">
            <ul role="list" class="mb-8">
                @foreach($activities as $key => $activity)
                    @if($activity['event'] === 'login')
                        <li>
                            <div class="relative pb-8">
                                @if(count($activities) != 1 && count($activities) != $key + 1)
                                    <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex items-start space-x-2">
                                    <div class="relative">
                                        <img class="flex size-10 items-center justify-center rounded-full bg-gray-400 dark:bg-gray-600 ring-8 ring-white dark:ring-gray-900" src="{{$activity['causer']->user_avatar}}" alt="">
                                        <span class="absolute -right-1 -bottom-0.5 rounded-tl bg-white dark:bg-gray-900 px-0.5 py-px">
                                <x-filament::icon :icon="\CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon::LogIn" class="size-5 p-0.5 text-gray-500 dark:text-gray-400"/>
                            </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div>
                                            <div class="text-sm">
                                                <x-filament::link :href="route('filament.digimon.resources.users.view', $activity['causer']->id)" class="font-medium text-gray-900 dark:text-gray-100">{{$activity['causer']->name}}</x-filament::link>
                                            </div>
                                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ucwords($activity['event'])}} {{\Carbon\Carbon::parse($activity['created_at'])->diffForHumans()}}</p>
                                        </div>
                                        <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                            <p>{{$activity['description']}}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endif
                    @if($activity['event'] === 'logout')
                        <li>
                            <div class="relative pb-8">
                                @if(count($activities) != 1 && count($activities) != $key + 1)
                                    <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex items-start space-x-2">
                                    <div class="relative">
                                        <img class="flex size-10 items-center justify-center rounded-full bg-gray-400 dark:bg-gray-600 ring-8 ring-white dark:ring-gray-900" src="{{$activity['causer']->user_avatar}}" alt="">
                                        <span class="absolute -right-1 -bottom-0.5 rounded-tl bg-white dark:bg-gray-900 px-0.5 py-px">
                                <x-filament::icon :icon="\CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon::LogOut" class="size-5 p-0.5 text-gray-500 dark:text-gray-400"/>
                            </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div>
                                            <div class="text-sm">
                                                <x-filament::link :href="route('filament.digimon.resources.users.view', $activity['causer']->id)" class="font-medium text-gray-900 dark:text-gray-100">{{$activity['causer']->name}}</x-filament::link>
                                            </div>
                                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ucwords($activity['event'])}} {{\Carbon\Carbon::parse($activity['created_at'])->diffForHumans()}}</p>
                                        </div>
                                        <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                            <p>{{$activity['description']}}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endif
                    @if($activity['event'] === 'idle')
                        <li>
                            <div class="relative pb-8">
                                @if(count($activities) != 1 && count($activities) != $key + 1)
                                    <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex items-start space-x-2">
                                    <div class="relative">
                                        <img class="flex size-10 items-center justify-center rounded-full bg-gray-400 dark:bg-gray-600 ring-8 ring-white dark:ring-gray-900" src="{{$activity['causer']->user_avatar}}" alt="">
                                        <span class="absolute -right-1 -bottom-0.5 rounded-tl bg-white dark:bg-gray-900 px-0.5 py-px">
                                <x-filament::icon :icon="\CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon::Clock" class="size-5 p-0.5 text-gray-500 dark:text-gray-400"/>
                            </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div>
                                            <div class="text-sm">
                                                <x-filament::link :href="route('filament.digimon.resources.users.view', $activity['causer']->id)" class="font-medium text-gray-900 dark:text-gray-100">{{$activity['causer']->name}}</x-filament::link>
                                            </div>
                                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ucwords($activity['event'])}} {{\Carbon\Carbon::parse($activity['created_at'])->diffForHumans()}}</p>
                                        </div>
                                        <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                                            <p>{!! $activity['description']  !!}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endif
                    @if($activity['event'] === 'calling')
                        <li>
                            <div class="relative pb-8">
                                <div class="relative flex items-start space-x-3">
                                    @if(count($activities) != 1 && count($activities) != $key + 1)
                                        <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                    @endif
                                    <div>
                                        <div class="relative px-1">
                                            <div class="flex size-8 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 ring-8 ring-white dark:ring-gray-900">
                                                <x-filament::icon :icon="\CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon::PhoneCall" class="size-5 text-gray-500 dark:text-gray-400 p-0.5"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1 py-1.5">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {!!
                                                 str_replace(
                                                    ['<strong>', '</strong>'],
                                                    ['<a href="'.route('filament.digimon.resources.services.view', $activity['subject_id']).'" class="text-primary-600 hover:underline font-bold"><strong>', '</strong></a>'],
                                                    $activity['description']
                                                )
                                            !!}
                                            <span class="whitespace-nowrap">{{\Carbon\Carbon::parse($activity['created_at'])->diffForHumans()}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endif
                    @if($activity['event'] === 'hangup')
                        <li>
                            <div class="relative pb-8">
                                <div class="relative flex items-start space-x-3">
                                    @if(count($activities) != 1 && count($activities) != $key + 1)
                                        <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                    @endif
                                    <div>
                                        <div class="relative px-1">
                                            <div class="flex size-8 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800 ring-8 ring-white dark:ring-gray-900">
                                                <x-filament::icon :icon="\CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon::PhoneOff" class="size-5 text-gray-500 dark:text-gray-400 p-0.5"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1 py-1.5">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {!!
                                                 str_replace(
                                                    ['<strong>', '</strong>'],
                                                    ['<a href="'.route('filament.digimon.resources.services.view', $activity['subject_id']).'" class="text-primary-600 hover:underline font-bold"><strong>', '</strong></a>'],
                                                    $activity['description']
                                                )
                                            !!}
                                            <span class="whitespace-nowrap">{{\Carbon\Carbon::parse($activity['created_at'])->diffForHumans()}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endif

</div>
