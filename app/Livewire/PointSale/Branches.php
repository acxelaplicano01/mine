<?php

namespace App\Livewire\PointSale;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.collapsable')]
class Branches extends Component
{
    public function render()
    {
        return view('livewire.point-sale.branches');
    }
}
