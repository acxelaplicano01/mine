<?php

namespace App\Livewire\Marketing;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.collapsable')]
class Marketing extends Component
{
    public function render()
    {
        return view('livewire.marketing.marketing');
    }
}
