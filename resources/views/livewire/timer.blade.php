<div>
    {{-- Knowing others is intelligence; knowing yourself is true wisdom. --}}
    @php
        use Illuminate\Support\Str;$nextRecord = $this->record?->next();
    @endphp
    <div class="flex justify-between">
           @if(!$sameId)
                <div class="flex space-x-0.5 dark:text-amber-500 text-gray-500">
                    <x-filament::icon :icon="\CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon::Timer"/>
                        <h1 class="font-bold text-center">Calling Timer</h1>
                </div>
                <div class="flex justify-center">
                    @if($nextRecord)
                        <x-filament::badge
                            color="info"
                            size="sm"
                            icon="heroicon-m-arrow-right"
                            icon-position="before">
                            Next: {{ Str::of($nextRecord->customer->customer_name)->limit(20) }}
                        </x-filament::badge>

                    @else
                        <x-filament::badge color="gray" icon="heroicon-o-check-circle">
                            No more customers
                        </x-filament::badge>
                    @endif

                </div>
           @endif

    </div>
    <div x-data="serviceTimer()" x-init="init()" class="space-y-4">
        @if($sameId)
            <div class="space-y-2">
                <x-filament::badge color="warning" size="lg">
                    <div class="flex items-center gap-1.5">
                                    <span class="relative flex h-2 w-2">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                    </span>
                        Processing
                    </div>
                </x-filament::badge>
                <h1 class="text-sm dark:text-gray-300 font-semibold">
                    {{$current_customer->customer->customer_name}}
                </h1>
            </div>
            <div class="space-y-2">
                <span class="text-xs font-semibold">Plate Number</span>
                <h1>{{$current_customer->vehicle->plate}}</h1>
            </div>
        @else
            <div class="flex justify-center">
                <template x-if="running">
                    <x-filament::badge color="warning" size="lg">
                        <div class="flex items-center gap-1.5">
                                <span class="relative flex h-2 w-2">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                </span>
                            Processing
                        </div>
                    </x-filament::badge>
                </template>
                <template x-if="!running">
                    <x-filament::badge color="gray" size="lg">
                        Idle
                    </x-filament::badge>
                </template>
            </div>
            <div class="flex justify-center">
                <div
                    class="bg-gradient-to-b from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-900 px-8 py-4 rounded-xl shadow-inner">
                    <span x-text="formattedTime"
                          class="font-mono font-bold text-3xl text-blue-600 dark:text-white tracking-widest"></span>
                </div>
            </div>
            <div class="flex justify-center gap-2">
                <template x-if="!running">
                    <x-filament::button
                        :color="\Filament\Support\Colors\Color::Indigo"
                        size="sm"
                        @click="start"
                        :icon="\CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon::Play">
                        Start Calling
                    </x-filament::button>
                </template>
                <template x-if="running">
                    <x-filament::button
                        color="danger"
                        size="sm"
                        @click="stop"
                        icon="heroicon-o-stop">
                        End Service & Next
                    </x-filament::button>
                </template>
            </div>
        @endif

    </div>
    @script
    <script>
        Alpine.data('serviceTimer', () => ({
            seconds: 0,
            running: false,
            interval: null,
            recordId: @js($record?->id),

            init() {
                this.checkExistingTimer();
            },

            async checkExistingTimer() {
                const data = await this.$wire.checkActiveTimer();
                if (data.active) {
                    this.seconds = data.elapsed;
                    this.running = true;
                    this.startInterval();
                }
            },

            startInterval() {
                this.stopInterval();
                this.interval = setInterval(() => this.seconds++, 1000);
            },

            stopInterval() {
                if (this.interval) {
                    clearInterval(this.interval);
                    this.interval = null;
                }
            },

            async start() {
                if (this.running || !this.recordId) return;

                this.running = true;
                this.seconds = 0;
                this.startInterval();

                try {
                    await this.$wire.handleCallStarted();
                } catch (error) {
                    console.error('Error starting service:', error);
                    this.reset();
                }
            },

            async stop() {
                if (!this.running || !this.recordId) return;

                this.stopInterval();
                this.running = false;

                try {
                    await this.$wire.handleCallEnded();
                    this.seconds = 0;
                } catch (error) {
                    console.error('Error ending service:', error);
                }
            },

            reset() {
                this.stopInterval();
                this.running = false;
                this.seconds = 0;
            },

            get formattedTime() {
                const hrs = Math.floor(this.seconds / 3600);
                const mins = Math.floor((this.seconds % 3600) / 60);
                const secs = this.seconds % 60;

                return [hrs, mins, secs]
                    .map(val => String(val).padStart(2, '0'))
                    .join(':');
            }
        }));
    </script>
    @endscript
</div>
