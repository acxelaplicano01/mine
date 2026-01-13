<?php

namespace App\Livewire\Transportista;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.collapsable')]
class Transportistas extends Component
{
    public function render()
    {
        return view('livewire.transportista.transportistas');
    }
}
