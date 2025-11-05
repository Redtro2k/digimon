<?php

namespace App\Livewire;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Livewire\Component;

class Feeds extends Component
{

    public $activities;

    public function render()
    {
        return view('livewire.feeds');
    }
}
