<?php

namespace App\Livewire;

use AllowDynamicProperties;
use App\Filament\Resources\Services\ServiceResource;
use App\Traits\QueueTimer;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[AllowDynamicProperties]
class Timer extends Component
{
    use QueueTimer;

    public string $view = 'livewire.timer';

    public ?Model $record = null;

    public bool $sameId = false;
    public $current, $current_customer;

    public function mount(): void
    {
        if($this->record) {
            $queue = auth()->user()
                ->queued()
                ->first();

            if($queue) {
                $this->current_customer = $queue->load('service')->service;
                $this->sameId = $this->record->id != $queue->service_id;
            }
        }
    }
    public function checkActiveTimer(): array
    {
        if (!$this->record) {
            return ['active' => false, 'elapsed' => 0];
        }

        $queue = $this->getActiveQueue();

        if ($queue?->started_at) {
            $elapsed = max(0, Carbon::parse($queue->started_at)->diffInSeconds(now()));

            return [
                'active' => true,
                'elapsed' => (int) $elapsed
            ];
        }

        return ['active' => false, 'elapsed' => 0];
    }
    public function handleCallStarted(): void
    {
        if (!$this->record) return;

        $this->updateQueueStatus('processing');

//        get first/ previous hangup for today
        $get_hang_up_activity = Activity::query()
            ->where('causer_id', auth()->id())
            ->where('event', 'hangup')
            ->whereDate('created_at', today())
            ->latest()
            ->first();

        if (!is_null($get_hang_up_activity)) {
            $duration = Carbon::parse($get_hang_up_activity->toArray()['properties']['end'])->diffForHumans(now(), [
                'parts' => 2,
                'short' => false,
                'syntax' => CarbonInterface::DIFF_ABSOLUTE
            ]);

            activity()
                ->useLog('logs')
                ->event('idle')
                ->causedBy(auth()->id())
                ->withProperties([
                    'roles' => auth()->user()->getRoleNames()->toArray(),
                    'idle_duration' => $duration,
                    'start_duration' => $get_hang_up_activity['properties']['end'],
                    'end_duration' => now()
                ])
                ->log(auth()->user()->name . " has been idle for " . "<strong>" . $duration . "</strong>" . ", indicating a period of inactivity since their last action.");
        }

        activity()
            ->useLog('logs')
            ->event('calling')
            ->performedOn($this->record)
            ->causedBy(auth()->id())
            ->withProperties(['roles' => auth()->user()->getRoleNames()->toArray()])
            ->log('Started calling Customer Service to Plate No.#' .'<strong>'. $this->record->vehicle->plate.'</strong>');

        Notification::make()
            ->title('Service Started')
            ->body('Your call to Customer Service is currently being handled.')
            ->success()
            ->send();

        $this->dispatch('refreshResourceForm');
    }

    public function handleCallEnded(): void
    {
        if (!$this->record) return;

        $queue = $this->getActiveQueue();

        if (!$queue?->started_at) {
            return;
        }

        $duration = $this->calculateDuration($queue->started_at);

        // for end

        $queue->update([
            'status'   => 'idle',
            'ended_at' => now(),
//            'duration' => $duration,
        ]);
        \Filament\Notifications\Notification::make()
            ->title('Service Ended')
            ->body("Your service has been successfully marked as idle. Duration: {$duration}")
            ->success()
            ->send();

        activity()
            ->useLog('logs')
            ->event('hangup')
            ->performedOn($this->record)
            ->causedBy(auth()->id())
            ->withProperties(['roles' => auth()->user()->getRoleNames()->toArray(), 'duration' => $duration, 'start' => $queue->started_at, 'end' => $queue->ended_at->toTimeString()])
            ->log('Ended the call with Customer Service regarding Plate No.#' .'<strong>'. $this->record->load('vehicle')->vehicle->plate.'</strong>'.
                ' (⏱:'.now()->shortAbsoluteDiffForHumans(now()->setTimeFromTimeString($queue->started_at)).')');

        $queue->delete();

        $this->dispatch('refreshResourceForm');

        $this->redirectToNextService($duration);
    }
    private function getActiveQueue()
    {
        return auth()->user()->queued()
            ->where('service_id', $this->record->id)
            ->where('status', 'processing')
            ->whereNotNull('started_at')
            ->whereNull('ended_at')
            ->first();
    }
    private function updateQueueStatus(string $status): void
    {
        auth()->user()->queued()->updateOrCreate(['service_id' => $this->record->id],
            [
                'status'     => $status,
                'started_at' => now(),
                'ended_at'   => null,
            ]
        );
    }

    private function redirectToNextService(string $duration): void
    {
        $nextService = $this->record->next();

        if (!$nextService) {
            Notification::make()
                ->title('Service Ended')
                ->body("Duration: {$duration}. No more services in queue.")
                ->success()
                ->send();
            return;
        }
        $this->redirect(ServiceResource::getUrl('view', ['record' => $nextService->id]));
    }
}
