<?php

namespace App\Livewire\Product;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.collapsable')]
class Colections extends Component
{
    public function render()
    {
        return view('livewire.producto.colections');
    }
}
