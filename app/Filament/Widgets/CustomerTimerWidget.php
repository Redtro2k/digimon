<?php

namespace App\Filament\Widgets;

use App\Models\Callout;
use Filament\Widgets\Widget;
class CustomerTimerWidget extends Widget
{
    protected string $view = 'filament.widgets.customer-timer-widget';

    public ?int $recordId = null;

    public function handleCallStarted()
    {
//        $callLog = Callout::query()->create([
//            'service_id' => $this->recordId,
//            'started_at' => now(),
//        ]);
//        $this->dispatch('call-id-received', callId: $callLog->id);
//
//        return $callLog['id'];
    }
}
