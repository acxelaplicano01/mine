<?php

namespace App\Livewire\Market;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.collapsable')]
class Markets extends Component
{
    public function render()
    {
        return view('livewire.market.markets');
    }
}
