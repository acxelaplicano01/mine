<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.collapsable')]
class Segments extends Component
{
    public function render()
    {
        return view('livewire.customer.segments');
    }
}
