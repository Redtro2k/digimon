<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class Timer extends Component
{
    public string $view = 'livewire.timer';
    public ?Model $record = null;

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
    }

    public function handleCallEnded(): void
    {
        if (!$this->record) return;
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
}
