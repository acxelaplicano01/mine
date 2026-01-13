<?php

namespace App\Livewire\Report;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.collapsable')]
class Reports extends Component
{
    public function render()
    {
        return view('livewire.report.reports');
    }
}
