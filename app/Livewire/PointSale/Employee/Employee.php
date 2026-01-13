<?php

namespace App\Livewire\PointSale\Employee;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.collapsable')]
class Employee extends Component
{
    public function render()
    {
        return view('livewire.point-sale.employee');
    }
}
