<x-filament-widgets::widget>
    <x-filament::section
        :icon="\CodeWithDennis\FilamentLucideIcons\Enums\LucideIcon::Timer"
        icon-color="primary"
    >
        <x-slot name="heading">
            Customer Timer Duration
        </x-slot>

        <x-slot name="description">
            Track call duration and save to database.
        </x-slot>

        <div x-data="callTimer()" x-init="init()" class="timer-widget">
            <!-- Timer Display -->
            <div class="timer-display">
                <x-filament::section.heading
                    x-text="formattedTime"
                    class="timer-text"
                />
            </div>

            <!-- Controls -->
            <div class="timer-buttons">
                <template x-if="!running">
                    <button class="btn btn-start" @click="start">
                        <x-filament::icon icon="heroicon-o-play" class="icon" />
                        Start Call
                    </button>
                </template>

                <template x-if="running">
                    <button class="btn btn-stop" @click="stop">
                        <x-filament::icon icon="heroicon-o-stop" class="icon" />
                        End Call
                    </button>
                </template>

                <button class="btn btn-reset" @click="reset">
                    <x-filament::icon icon="heroicon-o-arrow-path" class="icon" />
                    Reset
                </button>
            </div>
        </div>
    </x-filament::section>

    <style>
        .timer-widget {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
        }

        .timer-display {
            background: #f3f3f3;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        }

        .timer-text {
            font-family: monospace;
            font-weight: bold;
            font-size: 1.75rem; /* smaller font */
            color: #2563eb; /* primary blue */
            letter-spacing: 0.05em;
        }

        .timer-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }

        .btn {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.4rem 0.9rem;
            font-size: 0.9rem;
            font-weight: 600;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: background 0.2s ease-in-out;
        }

        .btn .icon {
            width: 1rem;
            height: 1rem;
        }

        .btn-start {
            background: #16a34a; /* green */
            color: white;
        }
        .btn-start:hover {
            background: #15803d;
        }

        .btn-stop {
            background: #dc2626; /* red */
            color: white;
        }
        .btn-stop:hover {
            background: #b91c1c;
        }

        .btn-reset {
            background: #e5e7eb; /* gray */
            color: #111827;
        }
        .btn-reset:hover {
            background: #d1d5db;
        }
    </style>
    @script
    <script>
        Alpine.data('callTimer', () => ({
            seconds: 0,
            running: false,
            interval: null,
            callId: null,

            init() {
                const saved = sessionStorage.getItem('callTimer');
                if (saved) {
                    const state = JSON.parse(saved);
                    this.seconds = state.seconds || 0;
                    this.callId = state.callId || null;
                    if (state.running) {
                        this.startInterval();
                        this.running = true;
                    }
                }

                // Listen for call ID from Livewire
                window.addEventListener('call-id-received', (event) => {
                    this.callId = event.detail.callId;
                    this.saveState();
                });
            },

            startInterval() {
                if (this.interval) {
                    clearInterval(this.interval);
                }
                this.interval = setInterval(() => {
                    this.seconds++;
                    this.saveState();
                }, 1000);
            },

            async start() {
                if (!this.running) {
                    this.running = true;

                    // Start the timer immediately
                    this.startInterval();

                    // Call Livewire to save start time
                    try {
                        await this.$wire.handleCallStarted();
                    } catch (error) {
                        console.error('Error starting call:', error);
                    }
                }
            },

            async stop() {
                this.running = false;
                if (this.interval) {
                    clearInterval(this.interval);
                    this.interval = null;
                }

                // Call Livewire to save end time
                if (this.callId) {
                    try {
                        await this.$wire.callEnded(this.callId, this.seconds);
                    } catch (error) {
                        console.error('Error ending call:', error);
                    }
                }

                this.saveState();
            },

            reset() {
                this.stop();
                this.seconds = 0;
                this.callId = null;
                sessionStorage.removeItem('callTimer');
            },

            saveState() {
                sessionStorage.setItem('callTimer', JSON.stringify({
                    seconds: this.seconds,
                    running: this.running,
                    callId: this.callId
                }));
            },

            get formattedTime() {
                const hrs = Math.floor(this.seconds / 3600);
                const mins = Math.floor((this.seconds % 3600) / 60);
                const secs = this.seconds % 60;

                return [hrs, mins, secs]
                    .map(val => val.toString().padStart(2, '0'))
                    .join(':');
            }
        }));
    </script>
    @endscript
</x-filament-widgets::widget>
