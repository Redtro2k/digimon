<?php

namespace App\Livewire;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Livewire\Component;

class Feeds extends Component
{

    public $activities;

    public function mount($activities = null){
        $this->activities = collect($activities)
            ->groupBy(fn ($activity) =>
            Carbon::parse($activity['created_at'])->isToday()
                ? 'Today'
                : (Carbon::parse($activity['created_at'])->isYesterday()
                ? 'Yesterday'
                : Carbon::parse($activity['created_at'])->format('F j, Y')
            )
            );
    }
    public function render()
    {
        return view('livewire.feeds');
    }
}
