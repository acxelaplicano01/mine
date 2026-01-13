<?php

namespace App\Livewire\Discount;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.collapsable')]
class Discounts extends Component
{
    public function render()
    {
        return view('livewire.discount.discounts');
    }
}
