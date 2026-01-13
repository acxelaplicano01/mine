<?php

namespace App\Livewire\OnlineStore;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.collapsable')]
class Themes extends Component
{
    public function render()
    {
        return view('livewire.online-store.themes');
    }
}
