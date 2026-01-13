<?php

namespace App\Livewire\Order;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.collapsable')]
class AbandonedOrders extends Component
{
    public function render()
    {
        return view('livewire.order.abandoned-orders');
    }
}
