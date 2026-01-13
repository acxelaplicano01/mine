<?php

namespace App\Livewire\OnlineStore;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.collapsable')]
class Pages extends Component
{
    public function render()
    {
        return view('livewire.online-store.pages');
    }
}
