<?php

namespace App\Livewire\Order;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.collapsable')]
class Drafts extends Component
{
    public function render()
    {
        return view('livewire.order.drafts');
    }
}
